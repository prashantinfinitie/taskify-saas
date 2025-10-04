<?php

namespace Plugins\AssetManagement\Imports;

use Carbon\Carbon;
use App\Models\Admin;
use Maatwebsite\Excel\Row;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Plugins\AssetManagement\Models\Asset;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Plugins\AssetManagement\Models\AssetHistory;
use Plugins\AssetManagement\Models\AssetCategory;

class AssetsImport implements OnEachRow, WithHeadingRow
{
    public $errors = []; // collect row errors

    public function onRow(Row $row)
    {
        $rowData = $row->toArray();

        $admin = Admin::where('user_id', auth()->id())->first();
        // Normalize purchase_date to Y-m-d if present
        if (!empty($rowData['purchase_date'])) {
            try {
                $rowData['purchase_date'] = Carbon::parse($rowData['purchase_date'])->format('Y-m-d');
            } catch (\Exception $e) {
                $this->errors[] = [
                    'row' => $row->getIndex(),
                    'messages' => ['Invalid purchase_date format.'],
                ];
                return; // skip this row if date is bad
            }
        }


        $rules = [
            'name' => 'required|string|max:255',
            'asset_tag' => 'required|string|unique:assets,asset_tag,NULL,id,admin_id,' . $admin->id,
            'description' => 'nullable|string',
            'category' => 'required|string|max:255',
            'status' => 'required|string|in:available,non-functional,lent,lost,damaged,under-maintenance',
            'purchase_date' => 'nullable|date|before:today',
            'purchase_cost' => 'nullable|numeric',
            'asset_image_url' => 'nullable|url',
        ];

        $validator = Validator::make($rowData, $rules);

        if ($validator->fails()) {
            $this->errors[] = [
                'row' => $row->getIndex(),
                'messages' => $validator->errors()->all(),
            ];
            return; // skip this row
        }
        $admin = Admin::where('user_id', auth()->id())->first();

        // Find or create category by name
        $category = AssetCategory::where('name', $rowData['category'])->first();

        if (!$category) {
            $category = AssetCategory::create([
                'name' => $rowData['category'],
                'admin_id' => $admin->id
            ]);
        }



        // Create asset
        $asset = Asset::create([
            'name' => $rowData['name'],
            'asset_tag' => $rowData['asset_tag'],
            'description' => $rowData['description'] ?? null,
            'category_id' => $category->id,
            'assigned_to' => null,
            'status' => $rowData['status'],
            'created_by' => Auth::id(),
            'purchase_cost' => $rowData['purchase_cost'],
            'purchase_date' => $rowData['purchase_date'],
            'admin_id' =>  $admin->id
        ]);

        AssetHistory::create([
            'asset_id' => $asset->id,
            'user_id' => auth()->id(),
            'action' => 'Created',
            'notes' => 'Asset created through bulk import',
        ]);

        if (!empty($rowData['asset_image_url'])) {
            $asset->addMediaFromUrl($rowData['asset_image_url'])
                ->toMediaCollection('asset-media');
        }
    }
}
