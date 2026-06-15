-- ============================================================
-- KLAIMY - Plateforme SaaS de Facturation Intelligente
-- Script SQL Complet - Base de données MySQL 8
-- ============================================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Base de données
CREATE DATABASE IF NOT EXISTS klaimy
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE klaimy;

-- ============================================================
-- TABLE: subscription_plans (Plans d'abonnement)
-- ============================================================
CREATE TABLE subscription_plans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    price_monthly DECIMAL(10,2) NOT NULL DEFAULT 0,
    price_yearly DECIMAL(10,2) NOT NULL DEFAULT 0,
    currency VARCHAR(3) NOT NULL DEFAULT 'XAF',
    max_invoices_per_month INT NOT NULL DEFAULT 10,
    max_users INT NOT NULL DEFAULT 1,
    max_warehouses INT NOT NULL DEFAULT 1,
    has_auto_recovery TINYINT(1) NOT NULL DEFAULT 0,
    has_watermark TINYINT(1) NOT NULL DEFAULT 1,
    has_priority_support TINYINT(1) NOT NULL DEFAULT 0,
    features JSON,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: companies (Entreprises / Tenants)
-- ============================================================
CREATE TABLE companies (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    legal_name VARCHAR(255),
    type ENUM('tpe','pme','grande_entreprise','association','ong','entrepreneur_individuel') NOT NULL DEFAULT 'tpe',
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    whatsapp VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    country VARCHAR(100) DEFAULT 'Cameroun',
    postal_code VARCHAR(20),
    tax_id VARCHAR(50) COMMENT 'Numéro contribuable',
    rccm VARCHAR(50) COMMENT 'Registre du Commerce',
    logo VARCHAR(255),
    website VARCHAR(255),
    default_currency VARCHAR(3) NOT NULL DEFAULT 'XAF',
    invoice_prefix VARCHAR(10) DEFAULT 'FAC',
    quote_prefix VARCHAR(10) DEFAULT 'DEV',
    delivery_prefix VARCHAR(10) DEFAULT 'BL',
    next_invoice_number INT UNSIGNED NOT NULL DEFAULT 1,
    next_quote_number INT UNSIGNED NOT NULL DEFAULT 1,
    next_delivery_number INT UNSIGNED NOT NULL DEFAULT 1,
    fiscal_year_start TINYINT NOT NULL DEFAULT 1,
    payment_terms INT NOT NULL DEFAULT 30 COMMENT 'Délai de paiement par défaut en jours',
    legal_mentions TEXT,
    payment_conditions TEXT,
    signature VARCHAR(255),
    plan_id INT UNSIGNED,
    subscription_status ENUM('trial','active','expired','cancelled') DEFAULT 'trial',
    subscription_start DATE,
    subscription_end DATE,
    invoices_this_month INT UNSIGNED NOT NULL DEFAULT 0,
    invoices_month_reset DATE,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_companies_email (email),
    INDEX idx_companies_country (country),
    INDEX idx_companies_plan (plan_id),
    INDEX idx_companies_status (subscription_status),
    FOREIGN KEY (plan_id) REFERENCES subscription_plans(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: users (Utilisateurs)
-- ============================================================
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    company_id INT UNSIGNED NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('owner','admin','accountant','commercial','cashier','collaborator') NOT NULL DEFAULT 'collaborator',
    permissions JSON,
    avatar VARCHAR(255),
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    email_verified_at TIMESTAMP NULL,
    last_login_at TIMESTAMP NULL,
    last_activity_at TIMESTAMP NULL,
    two_factor_required TINYINT(1) NOT NULL DEFAULT 0,
    two_factor_code VARCHAR(6),
    two_factor_expires_at TIMESTAMP NULL,
    password_reset_token VARCHAR(100),
    password_reset_expires_at TIMESTAMP NULL,
    login_attempts INT NOT NULL DEFAULT 0,
    locked_until TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE INDEX idx_users_email (email),
    INDEX idx_users_company (company_id),
    INDEX idx_users_role (role),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: sessions (Sessions utilisateur)
-- ============================================================
CREATE TABLE user_sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    session_token VARCHAR(128) NOT NULL UNIQUE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sessions_user (user_id),
    INDEX idx_sessions_token (session_token),
    INDEX idx_sessions_expires (expires_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: clients (Clients des entreprises)
-- ============================================================
CREATE TABLE clients (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    company_id INT UNSIGNED NOT NULL,
    type ENUM('individual','company') NOT NULL DEFAULT 'individual',
    name VARCHAR(255) NOT NULL,
    contact_name VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(20),
    whatsapp VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    country VARCHAR(100) DEFAULT 'Cameroun',
    postal_code VARCHAR(20),
    tax_id VARCHAR(50),
    rccm VARCHAR(50),
    notes TEXT,
    total_invoiced DECIMAL(15,2) NOT NULL DEFAULT 0,
    total_paid DECIMAL(15,2) NOT NULL DEFAULT 0,
    total_outstanding DECIMAL(15,2) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_clients_company (company_id),
    INDEX idx_clients_email (email),
    INDEX idx_clients_name (name),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: categories (Catégories produits/services)
-- ============================================================
CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    parent_id INT UNSIGNED,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_categories_company (company_id),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: products (Produits et Services)
-- ============================================================
CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    company_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED,
    type ENUM('product','service') NOT NULL DEFAULT 'product',
    name VARCHAR(255) NOT NULL,
    description TEXT,
    sku VARCHAR(50),
    unit VARCHAR(50) DEFAULT 'unité',
    price DECIMAL(15,2) NOT NULL DEFAULT 0,
    tax_rate DECIMAL(5,2) NOT NULL DEFAULT 19.25 COMMENT 'TVA par défaut Cameroun',
    cost_price DECIMAL(15,2) DEFAULT 0,
    stock_quantity INT DEFAULT 0,
    stock_alert_threshold INT DEFAULT 5,
    image VARCHAR(255),
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_products_company (company_id),
    INDEX idx_products_category (category_id),
    INDEX idx_products_type (type),
    INDEX idx_products_sku (sku),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: warehouses (Entrepôts)
-- ============================================================
CREATE TABLE warehouses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    address TEXT,
    city VARCHAR(100),
    phone VARCHAR(20),
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_warehouses_company (company_id),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: stock_movements (Mouvements de stock)
-- ============================================================
CREATE TABLE stock_movements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    warehouse_id INT UNSIGNED,
    type ENUM('in','out','transfer','adjustment') NOT NULL,
    quantity INT NOT NULL,
    reference VARCHAR(100),
    notes TEXT,
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_stock_company (company_id),
    INDEX idx_stock_product (product_id),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: quotes (Devis)
-- ============================================================
CREATE TABLE quotes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    company_id INT UNSIGNED NOT NULL,
    client_id INT UNSIGNED NOT NULL,
    quote_number VARCHAR(50) NOT NULL,
    status ENUM('draft','sent','viewed','accepted','rejected','converted') NOT NULL DEFAULT 'draft',
    issue_date DATE NOT NULL,
    expiry_date DATE,
    subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,
    tax_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    discount_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    discount_type ENUM('percentage','fixed') DEFAULT 'fixed',
    total DECIMAL(15,2) NOT NULL DEFAULT 0,
    currency VARCHAR(3) NOT NULL DEFAULT 'XAF',
    notes TEXT,
    terms TEXT,
    converted_invoice_id INT UNSIGNED,
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_quotes_company (company_id),
    INDEX idx_quotes_client (client_id),
    INDEX idx_quotes_status (status),
    INDEX idx_quotes_number (quote_number),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: invoices (Factures)
-- ============================================================
CREATE TABLE invoices (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    company_id INT UNSIGNED NOT NULL,
    client_id INT UNSIGNED NOT NULL,
    quote_id INT UNSIGNED,
    invoice_number VARCHAR(50) NOT NULL,
    status ENUM('draft','sent','viewed','paid','partial','overdue','cancelled') NOT NULL DEFAULT 'draft',
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,
    tax_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    discount_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    discount_type ENUM('percentage','fixed') DEFAULT 'fixed',
    total DECIMAL(15,2) NOT NULL DEFAULT 0,
    amount_paid DECIMAL(15,2) NOT NULL DEFAULT 0,
    amount_due DECIMAL(15,2) NOT NULL DEFAULT 0,
    currency VARCHAR(3) NOT NULL DEFAULT 'XAF',
    payment_link VARCHAR(255),
    qr_code VARCHAR(255),
    notes TEXT,
    terms TEXT,
    legal_mentions TEXT,
    is_recurring TINYINT(1) NOT NULL DEFAULT 0,
    recurring_interval ENUM('weekly','monthly','quarterly','yearly'),
    next_recurring_date DATE,
    created_by INT UNSIGNED,
    sent_at TIMESTAMP NULL,
    paid_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_invoices_company (company_id),
    INDEX idx_invoices_client (client_id),
    INDEX idx_invoices_status (status),
    INDEX idx_invoices_number (invoice_number),
    INDEX idx_invoices_due_date (due_date),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (quote_id) REFERENCES quotes(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: delivery_notes (Bons de livraison)
-- ============================================================
CREATE TABLE delivery_notes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    company_id INT UNSIGNED NOT NULL,
    client_id INT UNSIGNED NOT NULL,
    invoice_id INT UNSIGNED,
    delivery_number VARCHAR(50) NOT NULL,
    status ENUM('draft','sent','delivered') NOT NULL DEFAULT 'draft',
    delivery_date DATE NOT NULL,
    notes TEXT,
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_delivery_company (company_id),
    INDEX idx_delivery_client (client_id),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: document_items (Lignes de devis/factures/BL)
-- ============================================================
CREATE TABLE document_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_type ENUM('quote','invoice','delivery_note') NOT NULL,
    document_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED,
    description VARCHAR(500) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL DEFAULT 1,
    unit_price DECIMAL(15,2) NOT NULL DEFAULT 0,
    tax_rate DECIMAL(5,2) NOT NULL DEFAULT 0,
    tax_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    discount DECIMAL(15,2) NOT NULL DEFAULT 0,
    total DECIMAL(15,2) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    INDEX idx_items_document (document_type, document_id),
    INDEX idx_items_product (product_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: payments (Paiements)
-- ============================================================
CREATE TABLE payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    company_id INT UNSIGNED NOT NULL,
    invoice_id INT UNSIGNED NOT NULL,
    client_id INT UNSIGNED NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'XAF',
    payment_method ENUM('mobile_money_mtn','mobile_money_orange','card','wallet','cash','bank_transfer','other') NOT NULL,
    status ENUM('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
    transaction_id VARCHAR(255),
    cinetpay_payment_id VARCHAR(255),
    cinetpay_data JSON,
    reference VARCHAR(100),
    notes TEXT,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_payments_company (company_id),
    INDEX idx_payments_invoice (invoice_id),
    INDEX idx_payments_client (client_id),
    INDEX idx_payments_status (status),
    INDEX idx_payments_transaction (transaction_id),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: recovery_schedules (Planification e-recouvrement)
-- ============================================================
CREATE TABLE recovery_schedules (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    invoice_id INT UNSIGNED NOT NULL,
    client_id INT UNSIGNED NOT NULL,
    schedule_type ENUM('before_due','after_due') NOT NULL,
    days_offset INT NOT NULL COMMENT 'Négatif = avant échéance, positif = après',
    channel ENUM('email','whatsapp','sms') NOT NULL DEFAULT 'email',
    status ENUM('pending','sent','failed','cancelled') NOT NULL DEFAULT 'pending',
    scheduled_date DATE NOT NULL,
    sent_at TIMESTAMP NULL,
    message_template TEXT,
    message_sent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_recovery_company (company_id),
    INDEX idx_recovery_invoice (invoice_id),
    INDEX idx_recovery_date (scheduled_date),
    INDEX idx_recovery_status (status),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: recovery_templates (Modèles de relance)
-- ============================================================
CREATE TABLE recovery_templates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    channel ENUM('email','whatsapp','sms') NOT NULL,
    days_offset INT NOT NULL,
    subject VARCHAR(255),
    body TEXT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_templates_company (company_id),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: conversations (Messagerie)
-- ============================================================
CREATE TABLE conversations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    company_id INT UNSIGNED NOT NULL,
    type ENUM('internal','client') NOT NULL DEFAULT 'internal',
    subject VARCHAR(255),
    client_id INT UNSIGNED,
    created_by INT UNSIGNED,
    last_message_at TIMESTAMP NULL,
    is_archived TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_conv_company (company_id),
    INDEX idx_conv_client (client_id),
    INDEX idx_conv_last_msg (last_message_at),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: conversation_participants
-- ============================================================
CREATE TABLE conversation_participants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    last_read_at TIMESTAMP NULL,
    is_muted TINYINT(1) NOT NULL DEFAULT 0,
    UNIQUE INDEX idx_participant_unique (conversation_id, user_id),
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: messages
-- ============================================================
CREATE TABLE messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    conversation_id INT UNSIGNED NOT NULL,
    sender_id INT UNSIGNED,
    body TEXT NOT NULL,
    attachment VARCHAR(255),
    attachment_name VARCHAR(255),
    attachment_type VARCHAR(50),
    is_system_message TINYINT(1) NOT NULL DEFAULT 0,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_messages_conv (conversation_id),
    INDEX idx_messages_sender (sender_id),
    INDEX idx_messages_created (created_at),
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: notifications
-- ============================================================
CREATE TABLE notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    user_id INT UNSIGNED NOT NULL,
    company_id INT UNSIGNED NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    body TEXT,
    data JSON,
    channel ENUM('system','email','sms','whatsapp') NOT NULL DEFAULT 'system',
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    read_at TIMESTAMP NULL,
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_notif_user (user_id),
    INDEX idx_notif_company (company_id),
    INDEX idx_notif_read (is_read),
    INDEX idx_notif_type (type),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: activity_logs (Journal d'activité)
-- ============================================================
CREATE TABLE activity_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT UNSIGNED,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    old_data JSON,
    new_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_activity_company (company_id),
    INDEX idx_activity_user (user_id),
    INDEX idx_activity_entity (entity_type, entity_id),
    INDEX idx_activity_created (created_at),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: company_settings (Paramètres entreprise)
-- ============================================================
CREATE TABLE company_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT,
    UNIQUE INDEX idx_settings_unique (company_id, setting_key),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: super_admins (Administrateurs Klaimy)
-- ============================================================
CREATE TABLE super_admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: support_tickets
-- ============================================================
CREATE TABLE support_tickets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    subject VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
    priority ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
    assigned_to INT UNSIGNED,
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tickets_company (company_id),
    INDEX idx_tickets_status (status),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES super_admins(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: currencies
-- ============================================================
CREATE TABLE currencies (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(3) NOT NULL UNIQUE,
    name VARCHAR(50) NOT NULL,
    symbol VARCHAR(10) NOT NULL,
    exchange_rate DECIMAL(15,6) NOT NULL DEFAULT 1,
    is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- ============================================================
-- DONNEES INITIALES
-- ============================================================

-- Plans d'abonnement
INSERT INTO subscription_plans (name, slug, price_monthly, price_yearly, currency, max_invoices_per_month, max_users, max_warehouses, has_auto_recovery, has_watermark, has_priority_support, sort_order) VALUES
('Gratuit', 'free', 0, 0, 'XAF', 10, 1, 1, 0, 1, 0, 1),
('Starter', 'starter', 2500, 25000, 'XAF', 100, 1, 1, 0, 0, 0, 2),
('Pro', 'pro', 7500, 75000, 'XAF', -1, 2, 1, 1, 0, 0, 3),
('Business', 'business', 15000, 150000, 'XAF', -1, 10, 5, 1, 0, 0, 4),
('Enterprise', 'enterprise', 50000, 500000, 'XAF', -1, -1, -1, 1, 0, 1, 5);

-- Devises
INSERT INTO currencies (code, name, symbol, exchange_rate) VALUES
('XAF', 'Franc CFA CEMAC', 'FCFA', 1),
('XOF', 'Franc CFA UEMOA', 'FCFA', 1),
('USD', 'Dollar US', '$', 0.0016),
('EUR', 'Euro', '€', 0.0015),
('GBP', 'Livre Sterling', '£', 0.0013);

-- Super Admin par défaut
INSERT INTO super_admins (email, password_hash, name) VALUES
('admin@klaimy.com', '$argon2id$v=19$m=65536,t=4,p=1$placeholder', 'Super Admin');

SET FOREIGN_KEY_CHECKS = 1;
