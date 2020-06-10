<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\Task;
use DB, Cache;

class VisualizationController extends Controller
{
    //本周起止时间unix时间戳
    private $week_start;
    private $week_end;

    //本月起止时间unix时间戳
    private $month_start;
    private $month_end;

    function __construct()
    {
        $this->week_start = mktime(0, 0, 0, date("m"), date("d") - date("w") + 1, date("Y"));
        $this->week_end = mktime(23, 59, 59, date("m"), date("d") - date("w") + 7, date("Y"));

        $this->month_start = mktime(0, 0, 0, date("m"), 1, date("Y"));
        $this->month_end = mktime(23, 59, 59, date("m"), date("t"), date("Y"));
    }

    /**
     * 本周订单数
     * @return array
     */
    function sales_count()
    {
        return \Cache::remember('xApi_visualization_sales_count', 60, function () {
            $count = [];
            for ($i = 0; $i < 7; $i++) {
                $start = date('Y-m-d H:i:s', strtotime("+" . $i . " day", $this->week_start));
                $end = date('Y-m-d H:i:s', strtotime("+" . ($i + 1) . " day", $this->week_start));

                //待支付
                $count['create'][] = Order::whereBetween('created_at', [$start, $end])->where('status', 1)->count();

                $count['pay'][] = Order::whereBetween('pay_time', [$start, $end])->where('status', 2)->count();

                $count['shipping'][] = Order::whereBetween('shipping_time', [$start, $end])->where('status', 3)->count();

                $count['finish'][] = Order::whereBetween('finish_time', [$start, $end])->where('status', 5)->count();
                //申请退货
                $count['return_back'][] = Order::whereBetween('refund_time', [$start, $end])->where('status', 6)->count();
                //退货中
                $count['refund_index'][] = Order::whereBetween('refund_add_time', [$start, $end])->where('status', 9)->count();
                //退货完成
                $count['refund_suc'][] = Order::whereBetween('refund_suc_time', [$start, $end])->where('status', 10)->count();
            }

            $data = [
                'week_start' => date("Y年m月d日", $this->week_start),
                'week_end' => date("Y年m月d日", $this->week_end),
                'count' => $count,
            ];
            return $data;
        });

    }

    /**
     * 本周销售额
     * @return array
     */
    function sales_amount()
    {
        return \Cache::remember('xApi_visualization_sales_amount', 60, function () {
            $amount = [];
            for ($i = 0; $i < 7; $i++) {
                $start = date('Y-m-d H:i:s', strtotime("+" . $i . " day", $this->week_start));
                $end = date('Y-m-d H:i:s', strtotime("+" . ($i + 1) . " day", $this->week_start));
                $amount['create'][] = Order::whereBetween('created_at', [$start, $end])->where('status', 1)->sum('total_price');
                $amount['pay'][] = Order::whereBetween('pay_time', [$start, $end])->where('status', '>', 1)->sum('total_price');
            }

            $data = [
                'week_start' => date("Y年m月d日", $this->week_start),
                'week_end' => date("Y年m月d日", $this->week_end),
                'amount' => $amount,
            ];
            return $data;
        });
    }

    /**
     * 本月热门销量
     * @return mixed
     */
    function top()
    {
        return \Cache::remember('xApi_visualization_top', 60, function () {
//            DB::enableQueryLog();
            $start = date("Y-m-d H:i:s", $this->month_start);
            $end = date("Y-m-d H:i:s", $this->month_end);

            //本月订单的id
            $order = Order::whereBetween('created_at', [$start, $end])->pluck('id');

            //对应热门商品,前10名. 语句较复杂,请自己return sql出来看
            $products = OrderProduct::with('product')
                ->select('product_id', \DB::raw('sum(num) as sum_num'))
                ->whereIn('order_id', $order)
                ->groupBy('product_id')
                ->orderBy(\DB::raw('sum(num)'), 'desc')
                ->take(5)
                ->get();


            // return DB::getQueryLog();

            $data = [
                'month_start' => date("Y年m月d日", $this->month_start),
                'month_end' => date("Y年m月d日", $this->month_end),
                'products' => $products,
            ];
            return $data;
        });

    }

    /**
     * 商品浏览量统计
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function statistics_product()
    {
        $product_id = Activity::where(array('log_name' => 'product'))->pluck('subject_id');
        if (!empty($product_id)) {

            $products = Product::whereIn('id', $product_id)->get();

            foreach ($products as $key => $product) {
                $products[$key]['num'] = Activity::where(array('log_name' => 'product', 'subject_id' => $product['id']))->count();
                $products[$key]['title'] = $product->name;
                $products[$key]['order'] = OrderProduct::where('product_id', $product_id)->sum('num');
            }
        }

        return $products;
    }

    /**
     * 会员注册量
     * @return array
     */
    public function statistics_customer()
    {

        $year = date("Y", time());
        $num = [];
        for ($i = 1; $i <= 12; $i++) {
            $month = strlen($i) == 1 ? '0' . $i : $i;
            $like = $year . '_' . $month . '%';
            $num[] = Staff::where('created_at', 'like', $like)->count();
        }

        $data = [
            'this_year' => $year,
            'num' => $num
        ];
        return $data;
    }

    /**
     * 性别统计
     * @return \Illuminate\Support\Collection
     */
    function sex_count()
    {
        $male = Staff::where('sex', '1')->count();
        $female = Staff::where('sex', '2')->count();
        $other = Staff::where('sex', '0')->count();
        return collect(compact('male', 'female', 'other'));
    }

    /**
     * 省份统计
     * @return mixed
     */
    function customer_province()
    {
        $count = Staff::select(\DB::raw('province as name, count(*) as value'))->groupBy('province')->get();
        return $count;
    }

    /**
     * 签单情况统计
     */
    function chartjs()
    {
        //负责人
        $principal = [];
        $principals = Task::where('is_contract', true)->select('principal_id')->distinct()->get();
        foreach ($principals as $k => $v) {
            $principal[$k]['name'] = Staff::find($v->principal_id)->name;
            $principal[$k]['value'] = Task::where('principal_id', $v->principal_id)->count();
        }
        //对接人
        $access = [];
        $accesses = Task::where('is_contract', true)->select('access_id')->distinct()->get();
        foreach ($accesses as $k => $v) {
            $access[$k]['name'] = Staff::find($v->access_id)->name;
            $access[$k]['value'] = Task::where('access_id', $v->access_id)->count();
        }

        $legend = array_pluck($principal, 'name');

        $data = [
            'principal' => $principal,
            'access' => $access,
            'legend' => $legend,
        ];

        return $data;

    }
}
