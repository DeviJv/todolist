<?php

namespace App\Http\Controllers\Api;

use App\Enums\TodoPriorityEnum;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Todo;
use App\Traits\ResponseApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;

class TodoController extends Controller
{
    use ResponseApi;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $a_id = $request->get('activity_group_id');
            if ($a_id) {
                $todo = Todo::where('activity_id', $a_id)->get();
            } else {
                $todo = Todo::all();
            }
            return $this->success("Success", $todo);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required',
                'priority' => [new Enum(TodoPriorityEnum::class)],
                'activity_id' => 'required',
            ]);
            $activity = Activity::find($request->activity_id);
            if (!$activity) return $this->error("No activity with ID $request->activity_id", 404);

            $todo = new Todo;
            $todo->title = $request->title;
            $todo->priority = $request->priority;
            $todo->activity_id = $request->activity_id;
            $todo->save();
            return $this->success("Success", $todo);
        } catch (\Exception $e) {

            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $todo = Todo::find($id);

            if (!$todo) return $this->error("No todo with ID $id", 404);
            return $this->success("Success", $todo);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'title' => 'required',
                'priority' => [new Enum(TodoPriorityEnum::class)],
                'activity_id' => 'required',
            ]);

            $todo = Todo::find($id);
            if ($id && !$todo) return $this->error("No todo with ID $id", 404);

            $activity = Activity::find($request->activity_id);
            if (!$activity) return $this->error("No activity with ID $request->activity_id", 404);

            $todo->title = $request->title;
            $todo->priority = $request->priority;
            $todo->activity_id = $request->activity_id;
            $todo->save();
            return $this->success("Success", $todo);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $todo = Todo::find($id);
            if (!$todo) return $this->error("No todo with ID $id", 404);
            $todo->delete();
            DB::commit();
            return $this->success("Success", $todo);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    public function getAllTrashed()
    {
        try {
            $todo = Todo::onlyTrashed()->get();
            return $this->success("Success", $todo);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }
    public function restore($id = null)
    {
        try {
            if ($id) {
                $todo = Todo::onlyTrashed()->where('id', $id)->restore(); //restore byid
                //restore byid
                if (!$todo) return $this->error("No todo with ID $id", 404);
                return $this->success("Success", $todo);
            } else {
                $todo = Todo::onlyTrashed()->restore(); //restore all

                return $this->success("Success", $todo);
            }
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    public function delete_permanent($id)
    {
        DB::beginTransaction();
        try {
            $todo = Todo::withTrashed()->find($id);
            if (!$todo) return $this->error("No todo with ID $id", 404);
            $todo->forceDelete();
            DB::commit();
            return $this->success("Success", $todo);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->error($e->getMessage(), $e->getCode());
        }
    }
}