<?php
// app/Exports/DebitExportController.php
namespace App\Exports;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;

class DebitExportController implements FromCollection
{
  use Exportable;

  public function collection()
  {
    $user = Auth::user();
    $debitQuery = $this->getDebitQuery($user->id);
    $creditQuery = $this->getCreditQuery($user->id);

    $debitData = $debitQuery->get();
    $creditData = $creditQuery->get();

    return $debitData->merge($creditData);
  }

  private function getDebitQuery($userId)
  {
    return DB::table('debit')
      ->select('debit.id', 'debit.category_id', 'debit.user_id', 'debit.nominal', 'debit.debit_date', 'debit.description', 'categories_debit.id as id_category', 'categories_debit.name')
      ->leftJoin('categories_debit', 'debit.category_id', '=', 'categories_debit.id')
      ->where('debit.user_id', Auth::user()->id)
      ->orderBy('debit.created_at', 'DESC');
  }

  private function getCreditQuery($userId)
  {
    return DB::table('credit')
      ->select('credit.id', 'credit.category_id', 'credit.user_id', 'credit.nominal', 'credit.credit_date', 'credit.description', 'categories_credit.id as id_category', 'categories_credit.name')
      ->leftJoin('categories_credit', 'credit.category_id', '=', 'categories_credit.id')
      ->where('credit.user_id', Auth::user()->id)
      ->orderBy('credit.created_at', 'DESC');
  }
}
