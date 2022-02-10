<?php

public function index(Request $request)
{
    $min = $request->input('min');
    $max = $request->input('max');

    $start = $request->input('start_date');
    $end = $request->input('end_date');

    $query = Customer::query();

    if($min && $max) {
        $query->where('pending_payment', '>=', $min)->where('pending_payment', '<=', $max);
    }

    if ($start && $end) {

        $dateStart = date('Y-m-d 00:00:00', strtotime($start));
        $dateEnd = date('Y-m-d 23:59:00', strtotime($end));

        $query->whereBetween('created_at', [$dateStart, $dateEnd]);
    }

    $x = Datatables::of($query)
        ->editColumn('city_id', function ($x) {
            return Location::getCity($x->city_id);
        })
        ->editColumn('created_at', function ($x) {
            return \Carbon\Carbon::parse($x->created_at)->translatedFormat('d.m.Y');
        })
        ->editColumn('status', function ($x) {
            return $x->status ? '<span class="btn btn-success btn-sm">Aktif</span>' : '<span class="btn btn-warning btn-sm">Pasif</span>';
        })
        ->editColumn('pending_payment', function ($x) {
            return $x->pending_payment . ' TL';
        })
        ->addColumn('manage', function ($x) {
            $return = '<div class="dropdown dropdown d-inline">';
            $return.= '<button aria-expanded="false" aria-haspopup="true" class="btn btn-dark btn-sm" data-toggle="dropdown" type="button">İşlem <i class="fa fa-ellipsis-h ml-1"></i></button>';
            $return.= '<div class="dropdown-menu">';
            $return.= '<a class="dropdown-item text-warning" href="#" onclick="addNewPlan(' . $x->id . ', \'' . $x->name . '\')"><i class="typcn typcn-plus"></i> Planlama Oluştur</a>';
            $return.= '<a class="dropdown-item text-success" href="#" onclick="addNewTransaction(' . $x->id . ', \'' . $x->name . '\')"><i class="typcn typcn-plus"></i> Ödeme Al</a>';
            $return.= '<a class="dropdown-item text-danger" href="#" onclick="addNewOrder(' . $x->id . ', \'' . $x->name . '\')"><i class="typcn typcn-plus"></i> Sipariş Oluştur</a>';
            $return.= '<a class="dropdown-item text-primary" href="#" onclick="addNewInterest(' . $x->id . ', \'' . $x->name . '\')"><i class="typcn typcn-plus"></i> İlgilenilen Eğitimler</a>';
            $return.= '<a class="dropdown-item" href="' . route('customers.show', ['id' => $x->id]) . '"><i class="typcn typcn-eye"></i> Görüntüle</a>';
            $return.= '<a class="dropdown-item" href="' . route('customers.edit', ['id' => $x->id]) . '"><i class="typcn typcn-edit"></i> Düzenle</a>';
            $return.= '<a class="dropdown-item" href="' . route('customers.delete', ['id' => $x->id]) . '"><i class="typcn typcn-times"></i> Sil</a>';
            $return.= '</div>';
            $return.= '</div>';

            return $return;
        })
        ->rawColumns(['status', 'manage'])
        ->make(true);

    return $x;
}
