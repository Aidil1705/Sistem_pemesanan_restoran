<?php

declare(strict_types=1);

namespace App;

use Dompdf\Dompdf;

/**
 * Report service for PDF exports.
 */
class Report extends BaseModel
{
    /**
     * @param array<int, array<string, mixed>> $orders
     */
    public function streamHistory(array $orders): void
    {
        $html = '<h1>Riwayat Pemesanan talk caffe</h1><table width="100%" border="1" cellspacing="0" cellpadding="8">';
        $html .= '<tr><th>Pembeli</th><th>Meja</th><th>No. Pesanan</th><th>Status</th><th>Total</th></tr>';

        foreach ($orders as $order) {
            $html .= sprintf(
                '<tr><td>%s</td><td>%02d</td><td>%s</td><td>%s</td><td>Rp%s</td></tr>',
                htmlspecialchars($order['customer_name']),
                (int) $order['table_number'],
                htmlspecialchars($order['order_number']),
                htmlspecialchars($order['status']),
                number_format((int) $order['total'], 0, ',', '.')
            );
        }

        $html .= '</table>';
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('riwayat-pemesanan.pdf');
    }
}
