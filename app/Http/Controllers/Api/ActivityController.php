<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Todo;
use App\Traits\ResponseAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ActivityController extends Controller
{
    use ResponseAPI;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $activity = Activity::all();
            return $this->success("Success", $activity);
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
                'email' => 'required|unique:activities,email,except,id',
            ]);
            $activity = new Activity;
            $activity->title = $request->title;
            $activity->email = $request->email;
            $activity->save();
            return $this->success("Success", $activity);
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
            $activity = Activity::find($id);

            // Check the activity
            if (!$activity) return $this->error("No Activity with ID $id", 404);

            return $this->success("Success", $activity);
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
                'email' => ['required', Rule::unique('activities')->ignore($id),],
            ]);

            $activity = Activity::find($id);
            if ($id && !$activity) return $this->error("No activity with ID $id", 404);

            $activity->title = $request->title;
            $activity->email = $request->email;
            $activity->save();

            return $this->success("Success", $activity);
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
            $activity = activity::find($id);

            if (!$activity) return $this->error("No activity with ID $id", 404);

            $activity->todos()->delete();
            $activity->delete();
            DB::commit();
            return $this->success("Success", $activity);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    public function getAllTrashed()
    {
        try {
            $activity = Activity::onlyTrashed()->get();
            return $this->success("Success", $activity);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }
    public function restore($id = null)
    {
        try {
            if ($id) {
                $activity = Activity::onlyTrashed()->where('id', $id)->restore(); //restore byid
                $todo = Todo::onlyTrashed()->where('activity_id', $id)->restore(); //restore byid
                if (!$activity) return $this->error("No activity with ID $id", 404);
                return $this->success("Success", $activity);
            } else {
                $activity = Activity::onlyTrashed()->restore(); //restore all
                $todo = Todo::onlyTrashed()->restore(); //restore all
                return $this->success("Success", $activity);
            }
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    public function delete_permanent($id)
    {
        DB::beginTransaction();
        try {
            $activity = Activity::withTrashed()->find($id);
            if (!$activity) return $this->error("No activity with ID $id", 404);
            $activity->todos()->forceDelete();
            $activity->forceDelete();
            DB::commit();
            return $this->success("Success", $activity);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->error($e->getMessage(), $e->getCode());
        }
    }
}