# Laravel-FacturX (FacturaRO)

An enterprise-grade, decoupled financial microservice designed to ingest raw fiscal data and asynchronously generate compliant Factur-X PDF/A-3 documents. Built with a focus on clean architecture, this project addresses the inherent complexities and pains of modern B2B/B2C invoicing systems, specifically tailored for the Romanian legislative ecosystem (RO e-Factura / UBL 2.1 readiness).

## 🎯 Architecture & Design Philosophy

This service eschews the monolithic approach in favor of **Domain-Driven Design (DDD)** and the **Single Responsibility Principle**. It acts as a headless, API-only ingestion pipeline that protects the core domain logic from framework infrastructure.

Key architectural patterns implemented:

- **Builder Pattern (`InvoiceBuilder`):** Eliminates the telescoping constructor anti-pattern by providing a fluent interface to safely aggregate complex invoice data (suppliers, customers, line items, and dynamic VAT totals).
- **Strategy Pattern (`InvoiceRendererInterface`):** Ensures the system is Open for extension and Closed for modification (O/C in SOLID). Currently implements `DomPdfRendererStrategy` for human-readable PDFs, with a foundational architecture ready for `UblXmlRendererStrategy` to meet standard European EN 16931 and ANAF XML requirements.
- **Fail-Fast Defensive Validation:** Intercepts payload anomalies via strict `FormRequest` classes before they reach the domain or the message broker.

## 🇷🇴 Fiscal Compliance & Data Integrity

Financial data requires absolute precision. The domain layer enforces strict adherence to fiscal rules:

- **Algorithmic CUI/CIF Validation:** Custom Laravel validation rules implement the **Modulo 11** mathematical algorithm to verify Romanian tax identification numbers, ensuring no malformed identifiers enter the system.
- **Floating-Point Safety:** Monetary values and VAT rates (19%, 9%, 5%) are never calculated using standard PHP floats. The system uses the `BCMath` extension for high-precision arithmetic cross-validation between individual line items and the declared invoice total. Any discrepancy triggers a strict `422 Unprocessable Entity`.
- **Unicode Typography:** Full support for Romanian diacritics (ă, î, â, ș, ț) using UTF-8 meta-encoding and DejaVu Sans TrueType fonts within the PDF generation engine.

## 🚀 API & Asynchronous Workflow

Generating binary files (PDFs) is a resource-intensive process that should never block HTTP connections.

1.  **Ingestion:** The RESTful endpoint (`POST /api/v1/facturi`) accepts strongly typed JSON payloads.
2.  **Immediate Response:** Upon successful data validation, the controller dispatches a `GenerateInvoicePdfJob` to the message broker (Redis/RabbitMQ) and immediately returns a `202 Accepted` status with a unique job tracking ID.
3.  **Background Processing:** Background workers natively utilize `barryvdh/laravel-dompdf` to render the document, utilizing strictly controlled CSS for pagination (`page-break-inside: avoid`), and persist the binary via Laravel Flysystem (local storage, effortlessly extensible to AWS S3).

## 🛠 Tech Stack

- **Framework:** Laravel 11 (PHP 8.2+) - API Mode
- **Storage/Database:** SQLite (In-memory ready for instant TDD execution) / Flysystem
- **Queues:** Laravel Queues (Redis/RabbitMQ optimized)
- **PDF Engine:** `barryvdh/laravel-dompdf`
- **Testing:** Pest PHP (Strict Test-Driven Development leveraging expressive functional syntax and native Datasets for rigorous financial/VAT validation, built on PHPUnit)

## 📦 Local Setup & Testing

_(Zero-friction setup optimized for immediate code review)_

```bash
# Clone the repository
git clone [https://github.com/your-username/laravel-facturx.git](https://github.com/your-username/laravel-facturx.git)
cd laravel-facturx

# Install dependencies
composer install

# Configure environment (SQLite pre-configured for instant testing)
cp .env.example .env
php artisan key:generate
php artisan migrate

# Run the test suite
php artisan test
```
