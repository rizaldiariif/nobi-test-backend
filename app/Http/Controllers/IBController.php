<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\NAB;
use App\Models\UnitTransaction;
use Illuminate\Http\Request;

class IBController extends Controller
{
    public function updateTotalBalance(Request $request)
    {
        $body = $request->validate([
            'current_balance' => 'required'
        ]);

        $members = Member::all();

        if (count($members) == 0) {
            return NAB::create(['value' => 1]);
        }

        $total_unit = 0;

        foreach ($members as $member_key => $member) {
            if ($member->last_transaction) {
                $total_unit += $member->last_transaction->total_amount_unit;
            }
        }

        if ($total_unit == 0) {
            return NAB::create(['value' => 1]);
        }

        $raw_nab = $body['current_balance'] / $total_unit;
        $floored_nab = floor($raw_nab * 10000) / 10000;

        $created_nab = NAB::create(['value' => $floored_nab]);

        return ['nab_amount' => $created_nab->value];
    }

    public function listNAB()
    {
        return NAB::orderBy('created_at', 'desc')->select('value as nab', 'date')->get();
    }

    public function topup(Request $request)
    {
        $body = $request->validate([
            'user_id' => 'required',
            'amount_rupiah' => 'required'
        ]);

        $existing_member = Member::find($body['user_id']);

        if (!$existing_member) {
            return [
                'errors' => [
                    [
                        'user_id' => [
                            'User ID is not registered!'
                        ]
                    ]
                ]
            ];
        }

        $last_nab = NAB::orderBy('created_at', 'desc')->first();

        $raw_amount_unit = $body['amount_rupiah'] / $last_nab->value;
        $floored_amount_unit = floor($raw_amount_unit * 10000) / 10000;

        $last_transaction_total_rupiah = 0;
        $last_transaction_total_unit = 0;

        if ($existing_member->last_transaction) {
            $last_transaction_total_rupiah += $existing_member->last_transaction->total_amount_rupiah;
            $last_transaction_total_unit += $existing_member->last_transaction->total_amount_unit;
        }

        $created_transaction = UnitTransaction::create([
            'member_id' => $existing_member->id,
            'type' => 'topup',
            'amount_rupiah' => $body['amount_rupiah'],
            'amount_unit' => $floored_amount_unit,
            'total_amount_rupiah' => $last_transaction_total_rupiah + $body['amount_rupiah'],
            'total_amount_unit' => $last_transaction_total_unit + $floored_amount_unit
        ]);

        return [
            'nilai_unit_hasil_topup' => $created_transaction['amount_unit'],
            'nilai_unit_total' => $created_transaction['total_amount_unit'],
            'saldo_rupiah_total' => $created_transaction['total_amount_rupiah']
        ];
    }

    public function withdraw(Request $request)
    {
        $body = $request->validate([
            'user_id' => 'required',
            'amount_rupiah' => 'required'
        ]);

        $existing_member = Member::find($body['user_id']);

        if (!$existing_member) {
            return [
                'errors' => [
                    [
                        'user_id' => [
                            'User ID is not registered!'
                        ]
                    ]
                ]
            ];
        }

        if (!$existing_member->last_transaction) {
            return [
                'errors' => [
                    [
                        'user' => [
                            "This user don't have enough balance!"
                        ]
                    ]
                ]
            ];
        }

        $last_nab = NAB::orderBy('created_at', 'desc')->first();

        $raw_total_existing_unit_in_rupiah = $last_nab['value'] * $existing_member->last_transaction->total_amount_unit;
        $floored_total_existing_unit_in_rupiah = floor($raw_total_existing_unit_in_rupiah * 100) / 100;

        if ($body['amount_rupiah'] > $floored_total_existing_unit_in_rupiah) {
            return [
                'errors' => [
                    [
                        'user' => [
                            "This user don't have enough balance!"
                        ]
                    ]
                ]
            ];
        }

        $raw_amount_unit_withdraw = $body['amount_rupiah'] / $last_nab->value;
        $floored_amount_unit_withdraw = floor($raw_amount_unit_withdraw * 10000) / 10000;

        $created_transaction = UnitTransaction::create([
            'member_id' => $existing_member->id,
            'type' => 'withdraw',
            'amount_rupiah' => $body['amount_rupiah'],
            'amount_unit' => $floored_amount_unit_withdraw,
            'total_amount_rupiah' => $existing_member->last_transaction->total_amount_rupiah - $body['amount_rupiah'],
            'total_amount_unit' => $existing_member->last_transaction->total_amount_unit - $floored_amount_unit_withdraw
        ]);

        return [
            'nilai_unit_setelah_withdraw' => $created_transaction['amount_unit'],
            'nilai_unit_total' => $created_transaction['total_amount_unit'],
            'saldo_rupiah_total' => $created_transaction['total_amount_rupiah']
        ];
    }

    public function member(Request $request)
    {
        $body = $request->all();

        $page = 0;
        if (isset($body['page'])) {
            $page = $body['page'];
        }

        $limit = 20;
        if (isset($body['limit'])) {
            $limit = $body['limit'];
        }

        $query = Member::with(['last_transaction' => function ($query_transaction) {
            $query_transaction->select('member_id', 'total_amount_rupiah as total_amountrupiah_per_uid', 'total_amount_unit as total_unit_per_uid');
        }])->limit($limit)->offset($page * $limit);

        if (isset($body['user_id'])) {
            $query = $query->where('id', $body['user_id']);
        }

        $current_nab = NAB::orderBy('created_at', 'desc')->pluck('value')->first();

        return [
            'current_nab' => $current_nab,
            'members' => $query->select('id', 'name', 'username')->get()
        ];
    }
}
