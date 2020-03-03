<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('agent_id');
            $table->string('trade_id');
            $table->string('operator');
            $table->string('booking_channel');
            $table->string('payment_channel');
            $table->string('passengers');
            $table->integer('total_passengers');
            $table->integer('total_children');
            $table->string('phone');
            $table->string('email');
            $table->string('name');
            $table->string('paybill');
            $table->json('seats');
            $table->dateTime('departure_time');
            $table->dateTime('arrival_time');
            $table->float('total_amount');
            $table->float('refunded_amount');
            $table->boolean('booking_status');
            $table->json('confirmation_response');
            $table->integer('operator_confirmation_retries');
            $table->integer('operator_query_status_retries');
            $table->json('gateway_confirmation_response');
            $table->text('sms');
            $table->json('sms_confirmation_response');
            $table->string('qr_receipt');
            $table->text('qr_response');
            $table->string('email_receipt');
            $table->string('email_response');
            $table->string('sms_receipts_sent');
            $table->string('client_confirmation_sent');
            $table->string('client_confirmations_count');
            $table->string('source');
            $table->string('destination');
            $table->string('line');
            $table->string('custom_booking_no');
            $table->string('user');
            $table->dateTime('booking_date');
            $table->dateTime('date_of_travel');
            $table->string('referral_source');
            $table->string('referral_checked');
            $table->string('booking_organisation_id');
            $table->string('remote_reference');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bookings');
    }
}
