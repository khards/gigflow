<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MailTemplatesAddOwner extends Migration
{
    public function up()
    {
        Schema::table('mail_templates', function (Blueprint $table) {

            // Mail templates can be owned by different entities
            // The initial implementation they will be owned by a business
            $table->morphs('owner');
        });
    }
}
