<?php

/**
 * @file plugins/generic/coarNotifyReviewOffer/CoarNotifyReviewOfferSchemaMigration.php
 *
 * @class CoarNotifyReviewOfferSchemaMigration
 * @brief Describe database table structures.
 */

namespace APP\plugins\generic\coarNotifyReviewOffer\classes\migration;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CoarNotifyReviewOfferSchemaMigration extends Migration {
    /**
     * Run the migrations.
     * @return void
     */
    public function up(): void {
        if (!Schema::hasTable('review_offer_preferences')) {
            Schema::create('review_offer_preferences', function (Blueprint $table) {
                $table->string('service_url', 255);
                $table->bigInteger('submission_id');
                $table->boolean('is_sent');

                $table->foreign('submission_id')
                    ->references('submission_id')
                    ->on('submissions')
                    ->onDelete('cascade');
            });
        }
    }
}