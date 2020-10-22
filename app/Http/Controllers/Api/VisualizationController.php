<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Node;
use App\Models\Patron;
use App\Models\Project;
use App\Models\ProjectNode;
use App\Models\ProjectStaff;
use App\Models\Staff;
use App\Models\Task;
use DB, Cache;
use Illuminate\Http\Request;

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

    //产品项目分布图
    function project_p()
    {
        //产品
        $products = Staff::where('department_id', 18)->get();
        $name=Staff::where('department_id', 18)->pluck('name')->toarray();
        $count = [];
        foreach ($products as $product) {

            $project_ids=ProjectStaff::where('staff_id',$product->id)->pluck('project_id')->toarray();
            //已立项
            $count['create'][]=Project::where('status',1)->wherein('id',$project_ids)->count();
            //进行中
            $count['doing'][]=Project::where('status',2)->wherein('id',$project_ids)->count();
            //已暂停
            $count['stop'][]=Project::where('status',3)->wherein('id',$project_ids)->count();
            //已结项
            $count['finish'][]=Project::where('status',4)->wherein('id',$project_ids)->count();
        }
        $data = [
            'week_start' => date("Y年m月d日", $this->week_start),
            'week_end' => date("Y年m月d日", $this->week_end),
            'count' => $count,
            'name' => $name,
        ];
        return $data;
    }

    /**
     * 本月热门销量
     * @return mixed
     */
    function task_count()
    {
        return \Cache::remember('xApi_visualization_top', 60, function () {
//            DB::enableQueryLog();
            $start = date("Y-m-d H:i:s", $this->month_start);
            $end = date("Y-m-d H:i:s", $this->month_end);

            //本月订单的id
            $task = Task::whereBetween('created_at', [$start, $end])->pluck('id');

            //对应热门商品,前10名. 语句较复杂,请自己return sql出来看
            $tasks = Task::has('staff')->with('staff')
                ->select('staff_id', \DB::raw('sum(days) as sum_num'))
                ->whereIn('id', $task)
                ->groupBy('staff_id')
                ->orderBy(\DB::raw('sum(days)'), 'desc')
                //   ->take(5)
                ->get();


            // return DB::getQueryLog();

            $data = [
                'month_start' => date("Y年m月d日", $this->month_start),
                'month_end' => date("Y年m月d日", $this->month_end),
                'tasks' => $tasks,
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
        $principals = Task::where('is_contract', true)->select('staff_id')->distinct()->get();
        foreach ($principals as $k => $v) {
            $principal[$k]['name'] = Staff::find($v->staff_id)->name;
            $principal[$k]['value'] = Task::where('staff_id', $v->staff_id)->count();
        }
        //对接人
        $access = [];
        $accesses = Task::where('node_id', 1)->select('customer_id')->distinct()->get();
        foreach ($accesses as $k => $v) {
            $all = Task::where('customer_id', $v->customer_id)->where('node_id', 1)->count();
            $done = Task::where('customer_id', $v->customer_id)->where('node_id', 1)->where('is_contract', true)->count();
            if ($all == 0) {
                $rate = 0;
            } else {
                $rate = $done / $all;
            }
            $access[$k]['name'] = Customer::find($v->customer_id)->name . '(签约率:' . round($rate, 2) . ')';
            $access[$k]['value'] = Task::where('customer_id', $v->customer_id)->where('node_id', 1)->count();
        }

        $legend = array_pluck($access, 'name');

        //签约数量
        $contract = [];
        foreach ($accesses as $k => $v) {
            $contract[$k]['name'] = Customer::find($v->customer_id)->name;
            $contract[$k]['value'] = Task::where('customer_id', $v->customer_id)->where('node_id', 1)->where('is_contract', true)->count();
        }

        $data = [
            'principal' => $principal,
            'access' => $access,
            'legend' => $legend,
            'contract' => $contract,
        ];

        return $data;

    }

    public function task_rate()
    {
        //对接人
        $access = [];
        $accesses = Task::has('customer')->where('node_id', 1)->select('customer_id')->distinct()->get();
        foreach ($accesses as $k => $v) {
            $task = Task::where('customer_id', $v->customer_id)->count();
            $contract = Task::where('customer_id', $v->customer_id)->where('is_contract', true)->count();

            $access['name'][$k] = Customer::find($v->customer_id)->name;
            $access['task'][$k] = $task;
            $access['contract'][$k] = $contract;
            $access['rate'][$k] = $task ? $contract / $task : 0;
        }

        return $access;
    }

    public function case_count()
    {
        //负责人                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                
        $principal = [];
        $principals = Task::has('staff')->where('node_id', 1)->select('staff_id')->distinct()->get();
        foreach ($principals as $k => $v) {
            $task = Task::where('staff_id', $v->staff_id)->count();
            $contract = Task::where('staff_id', $v->staff_id)->where('is_contract', true)->count();

            $principal['name'][$k] = Staff::find($v->staff_id)->name;
            $principal['task'][$k] = $task;
            $principal['contract'][$k] = $contract;
            $principal['rate'][$k] = $task ? $contract / $task : 0;
        }

        return $principal;
    }

    public function task_days(Request $request)
    {
        $where = function ($query) use ($request) {
            if ($request->has('id') && $request->id != null) {
                $query->where('id', $request->id);
            }
            if ($request->has('name') && $request->name != null) {
                $query->where('name', 'like', '%' . $request->name . '%');
            }
            if ($request->has('staff_id') && $request->staff_id != null) {
                $query->where('staff_id', $request->staff_id);
            }
            if ($request->has('customer_id') && $request->customer_id != null) {
                $query->where('customer_id', $request->customer_id);
            }
            if ($request->has('is_contract') && $request->is_contract != null) {
                $query->where('is_contract', $request->is_contract);
            }

            if ($request->has('end') && $request->end != null) {
                $query->where('start_time', '<=', $request->end);
            }

            if ($request->has('start') && $request->start != null) {
                $query->where('start_time', '>=', $request->start);
            }
        };
        $task = [];
        $nodes = Node::where('is_task', true)->get();
        foreach ($nodes as $k => $node) {
            $task['node'][$k] = $node->name;
            $task['days'][$k]['value'] = Task::where($where)->where('node_id', $node->id)->sum('days');
            $task['days'][$k]['name'] = $node->name;
        }
        $task['all'] = Task::where($where)->sum('days');
        return $task;
    }

    public function project_count()
    {

        $nodes = Node::where('is_project', true)->get()->pluck('name')->toArray();
        $nodes_id = Node::where('is_project', true)->get()->pluck('id')->toArray();
        $projects = Project::all()->pluck('name')->toArray();
        $projects_id = Project::all()->pluck('id')->toArray();
        $series = [];
        $data_arr = [];
        $label = array('show' => true, 'position' => 'insideRight');
        foreach ($nodes_id as $k => $v) {
            foreach ($projects_id as $key => $project) {
                $data_arr[$key] = ProjectNode::where('node_id', $v)->where('project_id', $project)->sum('days');
            }
            $series[$k]['name'] = Node::find($v)->name;
            $series[$k]['type'] = 'bar';
            $series[$k]['data'] = $data_arr;
            $series[$k]['label'] = $label;
        }
        $data = [
            'node' => $nodes,
            'project' => $projects,
            'series' => $series,
        ];

        return $data;
    }

    /**
     * 员工项目情况分析图
     */
    public function staff_project()
    {
        //项目
        $projects = Project::all()->pluck('name')->toArray();
        $projects_ = Project::all();
        //项目节点
        $nodes = Node::where('is_project', true)->pluck('name')->toArray();
        $nodes_ = Node::where('is_project', true)->get();
        $node_arr = [];
        foreach ($nodes as $k => $node) {
            $node_arr[$k]['name'] = $node;
            $node_arr[$k]['type'] = 'bar';
        }
        //员工


        $department_ids = Node::wherein('name', $nodes)->pluck('department_id')->toarray();

        $staffs_ = Staff::wherein('department_id', $department_ids)->get();

        $staffs = Staff::wherein('department_id', $department_ids)->get()->pluck('name')->toArray();

        $staff_project_arr = [];
        foreach ($projects_ as $key => $project) {
            $staff_project_arr[$key]['title'] = ['text' => $project->name];
            foreach ($nodes_ as $k => $node) {
                foreach ($staffs_ as $kk => $staff) {
                    $staff_project_arr[$key]['series'][$k]['data'][$kk] = ProjectNode::where('node_id', $node->id)->where('project_id', $project->id)->where('staff_id', $staff->id)->sum('days');
                }
            }
        }

        $data = [
            'projects' => $projects,
            'nodes' => $nodes,
            'node_arr' => $node_arr,
            'staffs' => $staffs,
            'staff_project_arr' => $staff_project_arr,
        ];

        return $data;
    }

    public function project_status()
    {
        $status_1 = Project::where('status', 1)->count();
        $status_2 = Project::where('status', 2)->count();
        $status_3 = Project::where('status', 3)->count();
        $status_4 = Project::where('status', 4)->count();
        $status_5 = Project::where('is_check', 1)->count();

        $data = [$status_1, $status_2, $status_3, $status_4, $status_5];

        return $data;
    }

    public function delete_patron(Request $request)
    {
        $patron_id = $request->id;
        $customer_id = $request->customer_id;
        $patron = Patron::find($patron_id);
        if ($customer_id != $patron->customer_id) {
            return $this->error(500, '您没有权限权限！');
        }
        Patron::destroy($patron_id);
        return $this->null();
    }

    public function follow_edit(Request $request)
    {
        $patron_id = $request->id;
        $customer_id = $request->customer_id;
        $patron = Patron::find($patron_id);
        if ($customer_id != $patron->customer_id) {
            return $this->error(500, '您没有权限权限！');
        }
        $arr = [];
        if (!empty($request->follow_content)) {
            foreach ($request->follow_content as $k => $v) {
                if (empty($v['value'])) {
                    continue;
                }
                $arr[$k]['time'] = $request->follow_time[$k]['value'];
                $arr[$k]['content'] = $v['value'];
            }
        }
        $patron->follow = $arr;

        $patron->save();

        return $this->null();
    }

    public function upload_image(Request $request)
    {
        if ($request->id and $request->hasFile('file') and $request->file('file')->isValid()) {

            //文件大小判断$filePath
            $max_size = 1024 * 1024 * 3;
            $size = $request->file('file')->getClientSize();
            if ($size > $max_size) {
                return $this->error(500, '文件大小不能超过3M！');
            }

            $path = $request->file->store('upload', 'public');

            $patron = Patron::find($request->id)->toarray();
            array_push($patron['images'], '/' . $path);;
            Patron::where('id', $request->id)->update([
                'images' => json_encode(array_values($patron['images']))
            ]);

            return $this->null();
        }
    }

    public function delete_image(Request $request)
    {
        $patron_id = $request->id;
        $index = $request->index;
        $patron = Patron::find($patron_id);
        $images = $patron->images;
        foreach ($images as $key => $image) {
            if ($index == $key) {
                unset($images[$key]);
            }
        }
        Patron::where('id', $patron_id)->update([
            'images' => json_encode(array_values($images))
        ]);

        return $this->null();
    }


    public function notifications(Request $request)
    {
        $admin_id = $request->admin_id;
        $staff = Staff::where('admin_id', $admin_id)->first();

//        $staff->unreadNotifications; // 获取所有未读通知
//        $staff->readNotifications; // 获取所有已读通知
//        $staff->notifications; // 获取所有通知
        // 处理逻辑
        $count = isset($staff->unreadNotifications) ? count($staff->unreadNotifications) : 0;   // 获取的结果

        if ($count > 0) {
            $staff->markAsRead();
            return ['code' => 200, 'msg' => '您有新的消息请及时处理', 're' => $count];
        }
        return ['code' => 201];

    }


    public function customer_patron(Request $request)
    {
        $customerId = $request->get('q');

        $patrons = Patron::where('customer_id', $customerId)->get(['id', \DB::raw('name as text')]);

        return $patrons;
    }
}
