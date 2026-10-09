<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\IncomingDocument;
use App\Models\DocumentProcessing;
use App\Models\DocumentTaskLog;
use App\Models\ProjectDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class FileController extends Controller
{
    /**
     * Kiểm tra quyền truy cập tệp tin (Tối ưu hóa đa đối tượng Polymorphic)
     */
    private function authorizeAccess(Request $request, File $file): void
    {
        $user = $request->user();

        // 1. Admin mặc định có toàn quyền xem tất cả tệp
        if ($user->role === 'admin') {
            return;
        }

        $fileable = $file->fileable;

        if (!$fileable) {
            abort(404, 'Không tìm thấy dữ liệu liên kết với tệp tin.');
        }

        // 2. Phân luồng kiểm tra theo từng loại Model (Polymorphic Handler)
        $hasAccess = match (get_class($fileable)) {
            // Trường hợp 1: Hồ sơ dự án
            ProjectDocument::class => $this->authorizeProjectDocument($user, $fileable),

            // Trường hợp 2: Văn bản đến trực tiếp
            IncomingDocument::class => $this->authorizeIncomingDocument($user, $fileable),

            // Trường hợp 3: Báo cáo xử lý văn bản
            DocumentProcessing::class => $this->authorizeIncomingDocument($user, $fileable->incomingDocument),

            // Trường hợp 4: Nhật ký công việc văn bản
            DocumentTaskLog::class => $this->authorizeIncomingDocument($user, $fileable->processing?->incomingDocument),

            // Mặc định: Cho phép tài khoản đã đăng nhập truy cập (hoặc từ chối tùy chính sách)
            default => true,
        };

        if (!$hasAccess) {
            abort(403, 'Bạn không có quyền xem hoặc tải tệp tin này.');
        }
    }

    /**
     * Kiểm tra quyền truy cập Văn bản đến
     */
    private function authorizeIncomingDocument($user, ?IncomingDocument $document): bool
    {
        if (!$document) return false;

        return DocumentProcessing::where('incoming_document_id', $document->id)
            ->where(function ($query) use ($user) {
                $query->where('receiver_id', $user->id)
                      ->orWhere('sender_id', $user->id);
            })
            ->exists();
    }

    /**
     * Kiểm tra quyền truy cập Hồ sơ dự án
     */
    private function authorizeProjectDocument($user, ProjectDocument $doc): bool
    {
        // Cho phép tất cả người dùng trong hệ thống có quyền truy cập hồ sơ dự án (hoặc tùy biến theo dự án)
        return true;
    }

    /**
     * Hiển thị giao diện xem tệp (Inertia Preview)
     */
    public function preview(Request $request, File $file)
    {
        $this->authorizeAccess($request, $file);

        $fileable = $file->fileable;
        $title = $fileable?->title ?? $fileable?->document_code ?? $file->file_name;

        $streamUrl = route('files.stream', $file->id);

        return Inertia::render('DocumentViewer/Show', [
            'file' => [
                'id'            => $file->id,
                'file_name'     => $file->file_name,
                'extension'     => strtolower($file->file_type ?? pathinfo($file->file_name, PATHINFO_EXTENSION)),
                'url'           => $streamUrl,
                'document_code' => $title,
                'summary'       => $fileable?->category ?? 'Tệp đính kèm hồ sơ',
            ]
        ]);
    }

    /**
     * Stream trực tiếp tệp tin từ Storage/S3 về trình duyệt
     */
    public function stream(Request $request, File $file)
    {
        $this->authorizeAccess($request, $file);

        $disk = config('filesystems.default', 's3');

        if (!Storage::disk($disk)->exists($file->file_path)) {
            abort(404, 'Tệp tin không tồn tại trên hệ thống lưu trữ.');
        }

        return Storage::disk($disk)->response(
            $file->file_path,
            $file->file_name,
            [
                'Content-Type' => $file->mime_type ?? Storage::disk($disk)->mimeType($file->file_path),
                'Content-Disposition' => 'inline; filename="' . rawurlencode($file->file_name) . '"'
            ]
        );
    }
}