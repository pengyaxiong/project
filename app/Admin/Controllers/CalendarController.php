<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Calendar;
use Illuminate\Http\Request;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;

class CalendarController extends Controller
{
    public function store(Request $request)
    {
        Calendar::create([
            'title' => $request->title,
            'start' => $request->start,
            'end' => $request->end,
        ]);
    }

    public function show(Content $content, $id)
    {
        $calendar = Calendar::find($id);
        return $content
            ->title('订单号')
            ->description('')
            ->row(function (Row $row) use ($calendar) {
                $row->column(12, function (Column $column) use ($calendar) {
                    $column->append(new Box('评论详情...', view('admin.project.calendar', compact('calendar'))));
                });
            });

    }

    public function update(Request $request, $id)
    {
        Calendar::where('id', $id)->update([
            'title' => $request->title,
            'color' => $request->color,
            'start' => $request->start,
            'end' => $request->end,
        ]);
    }

    public function drop(Request $request, $id)
    {
        $data = Calendar::find($id);
        $start = date('Y-m-d G:i:s', strtotime($data['start'] . $request->day));
        $end = date('Y-m-d G:i:s', strtotime($data['end'] . $request->day));

        Calendar::where('id', $id)->update([
            'start' => $start,
            'end' => $end,
        ]);
    }

    public function event()
    {
        return Calendar::all();
    }
}
