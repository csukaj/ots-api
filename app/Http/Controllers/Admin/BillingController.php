<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\UserException;
use Exception;
use Illuminate\Http\Response;

class BillingController
{
    /**
     * @param int $orderId
     * @return Response
     * @throws Exception
     */
    public function billinggetpdf(int $orderId)
    {
        $billing = app('billing')->getPdf($orderId);
        if ($billing->hasError()) {
            throw new UserException('Error downloading pdf');
        }
        $pdfResponse = new Response($billing->getPdfInvoice(), 200);

        return ($pdfResponse);
    }
}