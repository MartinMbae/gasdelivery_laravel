<?php

use App\Models\GasCompany;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGasCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gas_companies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 20)->unique();
            $table->string('image')->nullable();
            $table->timestamps();
        });

        $data =  array(
            [
                'name' => 'Total',
            ],
        );
        foreach ($data as $datum){
            $category = new GasCompany();
            $category->name =$datum['name'];
            $category->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gas_companies');
    }
}
