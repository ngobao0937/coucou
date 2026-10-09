<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Disbursement;
use App\Models\DisbursementLog;
use Carbon\Carbon;

class MigrateOldDisbursementsToLogs extends Command
{
    protected $signature = 'disbursement:migrate-old-data';
    protected $description = 'Chuyển dữ liệu giải ngân nhập tay cũ sang dạng disbursement_logs';

    public function handle()
    {
        $disbursements = Disbursement::all();
        $count = 0;

        foreach ($disbursements as $item) {
            for ($month = 1; $month <= 12; $month++) {
                $amount = $item->{"month_{$month}"};

                if ($amount > 0) {
                    // Tạo ngày giả định là ngày cuối tháng đó
                    $date = Carbon::create($item->year, $month, 1)->endOfMonth()->format('Y-m-d');

                    // Kiểm tra xem đã có log tương ứng chưa để tránh trùng
                    $exists = DisbursementLog::where('project_id', $item->project_id)
                        ->whereYear('disbursement_date', $item->year)
                        ->whereMonth('disbursement_date', $month)
                        ->exists();

                    if (!$exists) {
                        DisbursementLog::withoutEvents(function () use ($item, $date, $amount) {
                            DisbursementLog::create([
                                'project_id'        => $item->project_id,
                                'disbursement_date' => $date,
                                'amount'            => $amount,
                                'note'              => 'Khởi tạo từ dữ liệu nhập tay cũ',
                            ]);
                        });
                        $count++;
                    }
                }
            }
        }

        $this->info("Đã chuyển đổi thành công {$count} bản ghi dữ liệu cũ sang DisbursementLogs!");
    }
}
