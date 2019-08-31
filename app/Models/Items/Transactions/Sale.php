<?php

namespace App\Models\Items\Transactions;

use App\Models\Items\Transactions\Transaction;
use Illuminate\Database\Eloquent\Model;
use Tightenco\Parental\HasChildren;

class Sale extends Transaction
{
    use HasChildren;

    protected $table = 'transactions';
}
