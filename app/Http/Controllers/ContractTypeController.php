<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContractType;

class ContractTypeController extends Controller
{
    public function getAll()
    {
        return ContractType::get();
    }
}
