<?php

namespace App\Domain\Invoice\Renderers;

use App\Domain\Invoice\Entities\Invoice;

class UblRendererStrategy implements InvoiceRendererInterface
{
    public function render(Invoice $invoice): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"></Invoice>');

        $xml->addChild('cbc:CustomizationID', 'urn:cen.eu:en16931:2017#compliant#urn:efactura.mfinante.ro:CIUS:RO1.0.0', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $xml->addChild('cbc:ID', $invoice->getSeries() . $invoice->getNumber(), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $xml->addChild('cbc:IssueDate', $invoice->getIssueDate()->format('Y-m-d'), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $xml->addChild('cbc:InvoiceTypeCode', '380', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $xml->addChild('cbc:DocumentCurrencyCode', 'RON', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        // Supplier
        $supplierPart = $xml->addChild('cac:AccountingSupplierParty', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $supplierParty = $supplierPart->addChild('cac:Party', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $supplierTax = $supplierParty->addChild('cac:PartyTaxScheme', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $supplierTax->addChild('cbc:CompanyID', $invoice->getSupplier()->getIdentifier()->getFullVatId(), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $taxScheme = $supplierTax->addChild('cac:TaxScheme', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $taxScheme->addChild('cbc:ID', 'VAT', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        
        $supplierName = $supplierParty->addChild('cac:PartyName', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $supplierName->addChild('cbc:Name', $invoice->getSupplier()->getName(), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        // Customer
        $customerPart = $xml->addChild('cac:AccountingCustomerParty', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $customerParty = $customerPart->addChild('cac:Party', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $customerTax = $customerParty->addChild('cac:PartyTaxScheme', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $customerTax->addChild('cbc:CompanyID', $invoice->getCustomer()->getIdentifier()->getFullVatId(), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $taxSchemeCust = $customerTax->addChild('cac:TaxScheme', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $taxSchemeCust->addChild('cbc:ID', 'VAT', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        $customerName = $customerParty->addChild('cac:PartyName', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $customerName->addChild('cbc:Name', $invoice->getCustomer()->getName(), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        // Legal Moneraty Totals
        $totals = $xml->addChild('cac:LegalMonetaryTotal', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $totals->addChild('cbc:LineExtensionAmount', $invoice->getTotalNet()->getAmount(), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2')->addAttribute('currencyID', 'RON');
        $totals->addChild('cbc:TaxExclusiveAmount', $invoice->getTotalNet()->getAmount(), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2')->addAttribute('currencyID', 'RON');
        $totals->addChild('cbc:TaxInclusiveAmount', $invoice->getTotalGross()->getAmount(), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2')->addAttribute('currencyID', 'RON');
        $totals->addChild('cbc:PayableAmount', $invoice->getTotalGross()->getAmount(), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2')->addAttribute('currencyID', 'RON');

        // Invoice Lines
        foreach ($invoice->getLines() as $index => $line) {
            $ublLine = $xml->addChild('cac:InvoiceLine', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            $ublLine->addChild('cbc:ID', (string)($index + 1), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            $ublLine->addChild('cbc:InvoicedQuantity', (string)$line->getQuantity(), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2')->addAttribute('unitCode', 'H87');
            $ublLine->addChild('cbc:LineExtensionAmount', $line->getNetTotal()->getAmount(), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2')->addAttribute('currencyID', 'RON');
            
            $item = $ublLine->addChild('cac:Item', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            $item->addChild('cbc:Name', $line->getDescription(), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            
            $price = $ublLine->addChild('cac:Price', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            $price->addChild('cbc:PriceAmount', $line->getUnitPrice()->getAmount(), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2')->addAttribute('currencyID', 'RON');
        }

        return $xml->asXML();
    }

    public function getExtension(): string
    {
        return 'xml';
    }
}
