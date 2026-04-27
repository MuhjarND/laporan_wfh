<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\WfhDate;

class WfhDateController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin']);
    }

    public function index(Request $request)
    {
        $query = WfhDate::query();

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', $request->bulan);
        }
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->tahun);
        }

        $wfhDates = $query->orderBy('tanggal', 'desc')->paginate(20);

        return view('admin.wfh-dates.index', compact('wfhDates'));
    }

    public function create()
    {
        return view('admin.wfh-dates.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $mulai = \Carbon\Carbon::parse($request->tanggal_mulai);
        $selesai = $request->tanggal_selesai
            ? \Carbon\Carbon::parse($request->tanggal_selesai)
            : $mulai->copy();

        $count = 0;
        while ($mulai->lte($selesai)) {
            WfhDate::updateOrCreate(
                ['tanggal' => $mulai->toDateString()],
                ['keterangan' => $request->keterangan, 'is_active' => true]
            );
            $mulai->addDay();
            $count++;
        }

        return redirect()->route('admin.wfh-dates.index')
            ->with('success', "{$count} tanggal WFH berhasil ditambahkan.");
    }

    public function destroy(WfhDate $wfhDate)
    {
        $wfhDate->delete();

        return redirect()->route('admin.wfh-dates.index')
            ->with('success', 'Tanggal WFH berhasil dihapus.');
    }

    public function toggleActive(WfhDate $wfhDate)
    {
        $wfhDate->update(['is_active' => !$wfhDate->is_active]);

        return redirect()->route('admin.wfh-dates.index')
            ->with('success', 'Status tanggal WFH berhasil diperbarui.');
    }
}
