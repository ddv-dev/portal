<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Проверяем, есть ли колонки в attachments
        $hasNewId = Schema::hasColumn('attachments', 'new_id');
        $hasSopId = Schema::hasColumn('attachments', 'sop_id');
        $hasOrderId = Schema::hasColumn('attachments', 'order_id');

        // Если колонок нет, переносить нечего
        if (!$hasNewId && !$hasSopId && !$hasOrderId) {
            return;
        }

        // Получаем вложения с существующими связями
        $attachments = DB::table('attachments')->get();
        
        foreach ($attachments as $attachment) {
            $linkableType = null;
            $linkableId = null;
            
            // Проверяем каждую колонку
            if ($hasNewId && isset($attachment->new_id) && $attachment->new_id) {
                $linkableType = 'news';
                $linkableId = $attachment->new_id;
            } elseif ($hasSopId && isset($attachment->sop_id) && $attachment->sop_id) {
                $linkableType = 'sop';
                $linkableId = $attachment->sop_id;
            } elseif ($hasOrderId && isset($attachment->order_id) && $attachment->order_id) {
                $linkableType = 'order';
                $linkableId = $attachment->order_id;
            }
            
            if ($linkableType && $linkableId) {
                // Проверяем, нет ли уже такой связи
                $exists = DB::table('attachment_links')
                    ->where('attachment_id', $attachment->id)
                    ->where('linkable_type', $linkableType)
                    ->where('linkable_id', $linkableId)
                    ->exists();
                
                if (!$exists) {
                    DB::table('attachment_links')->insert([
                        'attachment_id' => $attachment->id,
                        'linkable_type' => $linkableType,
                        'linkable_id' => $linkableId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down()
    {
        DB::table('attachment_links')->truncate();
    }
};