<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\App;
use App\Models\Payment;
use App\Models\Invoice;

class PaymentController extends Controller
{
    private Payment $paymentModel;
    private Invoice $invoiceModel;

    public function __construct()
    {
        $this->paymentModel = new Payment();
        $this->invoiceModel = new Invoice();
    }

    public function index(): void
    {
        $page = (int) ($this->input('page', 1));
        $result = $this->paymentModel->getForCompany($this->companyId(), $page);

        $layout = 'app';
        $pageTitle = 'Paiements';
        $currentPage = 'payments';
        $payments = $result['items'];
        $pagination = $result;
        $this->view('payments.index', compact('layout', 'pageTitle', 'currentPage', 'payments', 'pagination'));
    }

    public function record(string $invoiceId): void
    {
        if (!$this->verifyCsrf()) {
            $this->setFlash('error', 'Token de sécurité invalide.');
            $this->redirect("/invoices/{$invoiceId}");
            return;
        }

        $invoice = $this->invoiceModel->find((int) $invoiceId);
        if (!$invoice || $invoice['company_id'] !== $this->companyId()) {
            $this->setFlash('error', 'Facture introuvable.');
            $this->redirect('/invoices');
            return;
        }

        $amount = (float) $this->input('amount', 0);
        $method = $this->input('payment_method', 'cash');

        if ($amount <= 0) {
            $this->setFlash('error', 'Le montant doit être supérieur à 0.');
            $this->redirect("/invoices/{$invoiceId}");
            return;
        }

        try {
            $this->paymentModel->recordPayment((int) $invoiceId, $amount, $method);
            $this->setFlash('success', 'Paiement enregistré avec succès.');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Erreur lors de l\'enregistrement du paiement.');
        }

        $this->redirect("/invoices/{$invoiceId}");
    }

    /**
     * Public payment page (accessible without auth via invoice UUID)
     */
    public function paymentPage(string $uuid): void
    {
        $invoice = $this->invoiceModel->findBy('uuid', $uuid);
        if (!$invoice) {
            http_response_code(404);
            echo 'Facture introuvable.';
            return;
        }

        $invoice = $this->invoiceModel->getWithDetails($invoice['id']);

        // Mark as viewed
        if ($invoice['status'] === 'sent') {
            $this->invoiceModel->update($invoice['id'], ['status' => 'viewed']);
        }

        $pageTitle = 'Payer facture ' . $invoice['invoice_number'];
        $this->view('payments.public_pay', compact('pageTitle', 'invoice'));
    }

    /**
     * Initialize CinetPay payment
     */
    public function initCinetPay(): void
    {
        $invoiceUuid = $this->input('invoice_uuid', '');
        $invoice = $this->invoiceModel->findBy('uuid', $invoiceUuid);

        if (!$invoice) {
            $this->json(['error' => 'Facture introuvable.'], 404);
            return;
        }

        $config = App::getInstance()->config('cinetpay');
        $appUrl = App::getInstance()->config('app.url');

        $transactionId = 'KLM-' . time() . '-' . $invoice['id'];

        $paymentData = [
            'apikey' => $config['api_key'],
            'site_id' => $config['site_id'],
            'transaction_id' => $transactionId,
            'amount' => (int) $invoice['amount_due'],
            'currency' => $invoice['currency'],
            'description' => 'Paiement facture ' . $invoice['invoice_number'],
            'notify_url' => $appUrl . $config['notify_url'],
            'return_url' => $appUrl . "/pay/{$invoiceUuid}?status=return",
            'channels' => 'ALL',
            'lang' => 'FR',
            'customer_name' => $invoice['client_name'] ?? '',
            'customer_email' => $invoice['client_email'] ?? '',
            'customer_phone_number' => $invoice['client_phone'] ?? '',
        ];

        // Create pending payment
        $this->paymentModel->createPayment([
            'company_id' => $invoice['company_id'],
            'invoice_id' => $invoice['id'],
            'client_id' => $invoice['client_id'],
            'amount' => $invoice['amount_due'],
            'currency' => $invoice['currency'],
            'payment_method' => 'mobile_money_mtn',
            'status' => 'pending',
            'transaction_id' => $transactionId,
        ]);

        // Call CinetPay API
        $ch = curl_init($config['base_url']);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($paymentData),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $result = json_decode($response, true);
            $this->json($result);
        } else {
            $this->json(['error' => 'Erreur de communication avec CinetPay.', 'code' => $httpCode], 500);
        }
    }

    /**
     * CinetPay webhook handler
     */
    public function cinetPayWebhook(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['cpm_trans_id'])) {
            http_response_code(400);
            return;
        }

        $transactionId = $input['cpm_trans_id'];

        // Verify with CinetPay
        $config = App::getInstance()->config('cinetpay');
        $verifyData = [
            'apikey' => $config['api_key'],
            'site_id' => $config['site_id'],
            'transaction_id' => $transactionId,
        ];

        $ch = curl_init('https://api-checkout.cinetpay.com/v2/payment/check');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($verifyData),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        $payment = $this->paymentModel->findBy('transaction_id', $transactionId);
        if (!$payment) {
            http_response_code(404);
            return;
        }

        $status = $response['data']['status'] ?? 'REFUSED';

        if ($status === 'ACCEPTED') {
            $this->paymentModel->update($payment['id'], [
                'status' => 'completed',
                'paid_at' => date('Y-m-d H:i:s'),
                'cinetpay_data' => json_encode($response['data']),
            ]);

            $this->invoiceModel->updateAmounts($payment['invoice_id']);
        } else {
            $this->paymentModel->update($payment['id'], [
                'status' => 'failed',
                'cinetpay_data' => json_encode($response['data'] ?? []),
            ]);
        }

        http_response_code(200);
        echo 'OK';
    }
}
