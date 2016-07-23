<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Validator, Log, Exception;

use App\Http\Controllers\BaseController;

use App\Contracts\UserInterface as User;
use App\Contracts\CategoryInterface as Category;
use App\Contracts\RepetitionInterface as Repetition;
use App\Contracts\AlertInterface as Alert;

class AlertController extends BaseController
{
    public function __construct(
        User $user,
        Category $category,
        Repetition $repetition,
        Alert $alert)
    {
        $this->user = $user;
        $this->category = $category;
        $this->repetition = $repetition;
        $this->alert = $alert;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        echo 'hi';
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
            // Validate the request
            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|numeric',
                'category_id'       => 'required|numeric',
                // 'name'              => 'required', optional
                'location'          => 'required',
                'scheduled_time'    => 'required|date',
                'repetition_id'     => 'required|numeric'
            ]);
            if ($validator->fails()) {
                throw new Exception(json_to_string($validator->messages()->toArray()));
            }

            $user_id = $request->input('user_id');
            $category_id = $request->input('category_id');
            $repetition_id = $request->input('repetition_id');

            // VALIDATION - START

            // check if user id exists and active
            if (! $this->user->exists(['id' => $user_id, 'active' => 1])) {
                throw new Exception("Error Processing Request: Invalid User");
            }

            // check if valid category
            if (! $this->category->exists(['id' => $category_id])) {
                throw new Exception("Error Processing Request: Invalid Category");
            }

            // check if valid repetition
            if (! $this->repetition->exists(['id' => $repetition_id])) {
                throw new Exception("Error Processing Request: Invalid Repetition");
            }
            // VALIDATION - END

            $data = [
                'category_id' => $category_id,
                'user_id' => $user_id,
                'name' => $request->input('name'),
                'location' => $request->input('location'),
                'scheduled_time' => $request->input('scheduled_time'),
                'repetition_id' => $repetition_id
            ];

            if (! $this->alert->store($data)) {
                throw new Exception("Error Processing Request: Cannot store alert");
            }

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id user id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            // START VALIDATION
            if (empty($id)) {
                throw new Exception("Error Processing Request: User ID is required.");
            }

            // Check if user exists and active
            if (! $this->user->exists(['id' => $id, 'active' => 1])) {
                throw new Exception("Error Processing Request: Invalid User");
            }
            //  END VALIDATION

            $alerts = $this->alert->getList([
                'where' => [
                    'and' => [
                        ['field' => 'user_id', 'value' => $id]
                    ]
                ],
                'order_by' => ['id' => 'desc']
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $alerts
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
