<?php

namespace App\Http\Controllers\Characters;

use Illuminate\Http\Request;

use DB;
use Auth;
use Settings;
use App\Models\Item\Item;
use App\Models\User\User;
use App\Models\User\UserItem;
use App\Models\Character\Character;
use App\Models\Character\CharacterDesignUpdate;
use App\Models\Species\Species;
use App\Models\Species\Subtype;
use App\Models\Rarity;
use App\Models\Feature\Feature;
use App\Models\Item\ItemCategory;
use App\Services\CharacterManager;

use App\Http\Controllers\Controller;

class DesignController extends Controller
{
    /**
     * Shows the index of character design update submissions.
     *
     * @param  string  $type
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDesignUpdateIndex($type = null)
    {
        $requests = CharacterDesignUpdate::where('user_id', Auth::user()->id);
        if(!$type) $type = 'draft';
        $requests->where('status', ucfirst($type));

        return view('character.design.index', [
            'requests' => $requests->orderBy('id', 'DESC')->paginate(20),
            'status' => $type
        ]);
    }

    /**
     * Shows a design update request.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDesignUpdate($id)
    {
        $r = CharacterDesignUpdate::find($id);
        if(!$r || ($r->user_id != Auth::user()->id && !Auth::user()->hasPower('manage_characters'))) abort(404);
        return view('character.design.request', [
            'request' => $r
        ]);
    }

    /**
     * Shows a design update request's comments section.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getComments($id)
    {
        $r = CharacterDesignUpdate::find($id);
        if(!$r || ($r->user_id != Auth::user()->id && !Auth::user()->hasPower('manage_characters'))) abort(404);
        return view('character.design.comments', [
            'request' => $r
        ]);
    }

    /**
     * Edits a design update request's comments section.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\CharacterManager  $service
     * @param  int                            $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postComments(Request $request, CharacterManager $service, $id)
    {
        $r = CharacterDesignUpdate::find($id);
        if(!$r) abort(404);
        if($r->user_id != Auth::user()->id) abort(404);

        if($service->saveRequestComment($request->only(['comments']), $r)) {
            flash('Request edited successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Shows a design update request's image section.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getImage($id)
    {
        $r = CharacterDesignUpdate::find($id);
        if(!$r || ($r->user_id != Auth::user()->id && !Auth::user()->hasPower('manage_characters'))) abort(404);
        return view('character.design.image', [
            'request' => $r,
            'users' => User::query()->orderBy('name')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Edits a design update request's image upload section.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\CharacterManager  $service
     * @param  int                            $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postImage(Request $request, CharacterManager $service, $id)
    {
        $r = CharacterDesignUpdate::find($id);
        if(!$r) abort(404);
        if($r->user_id != Auth::user()->id && !Auth::user()->hasPower('manage_characters')) abort(404);
        $request->validate(CharacterDesignUpdate::$imageRules);

        $useAdmin = ($r->status != 'Draft' || $r->user_id != Auth::user()->id) && Auth::user()->hasPower('manage_characters');
        if($service->saveRequestImage($request->all(), $r, $useAdmin)) {
            flash('Request edited successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Shows a design update request's addons section.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getAddons($id)
    {
        $r = CharacterDesignUpdate::find($id);
        if(!$r || ($r->user_id != Auth::user()->id && !Auth::user()->hasPower('manage_characters'))) abort(404);
        if($r->status == 'Draft' && $r->user_id == Auth::user()->id) 
            $inventory = UserItem::with('item')->whereNull('deleted_at')->where('count', '>', '0')->where('user_id', $r->user_id)->get();
        else 
            $inventory = isset($r->data['user']) ? parseAssetData($r->data['user']) : null;
        return view('character.design.addons', [
            'request' => $r,
            'categories' => ItemCategory::orderBy('sort', 'DESC')->get(),
            'inventory' => $inventory,
            'items' => Item::all()->keyBy('id'),
            'item_filter' => Item::orderBy('name')->get()->keyBy('id'),
            'page' => 'update'
        ]);
    }

    /**
     * Edits a design update request's addons section.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\CharacterManager  $service
     * @param  int                            $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postAddons(Request $request, CharacterManager $service, $id)
    {
        $r = CharacterDesignUpdate::find($id);
        if(!$r) abort(404);
        if($r->user_id != Auth::user()->id) abort(404);

        if($service->saveRequestAddons($request->all(), $r)) {
            flash('Request edited successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Shows a design update request's features section.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFeatures($id)
    {
        $r = CharacterDesignUpdate::find($id);
        $isMyo = Character::where('id', $r->character_id)->pluck('is_myo_slot')->first();  
        $isFreeMyo = Character::where('id', $r->character_id)->pluck('is_free_myo')->first();  
        $hasSpeciesUsable = Species::where('is_free_myo_usable', 1)->count() != 0;
        $hasSubtypeUsable = Subtype::where('is_free_myo_usable', 1)->count() != 0;
        if(!$r || ($r->user_id != Auth::user()->id && !Auth::user()->hasPower('manage_characters'))) abort(404);
        if(!$isFreeMyo){
            $speciesDropdown = ['0' => 'Select Species'] + Species::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray();
            $subtypeDropdown = ['0' => 'No Subtype'] + Subtype::where('species_id','=',$r->species_id)->orderBy('sort', 'DESC')->pluck('name', 'id')->toArray();
        } else {
            $speciesDropdown = ['0' => 'Select Species'] + Species::where('is_free_myo_usable', 1)->orderBy('sort', 'DESC')->pluck('name', 'id')->toArray();
            $subtypeDropdown = ['0' => 'No Subtype'] + Subtype::where('species_id','=',$r->species_id)->where('is_free_myo_usable', 1)->orderBy('sort', 'DESC')->pluck('name', 'id')->toArray();
        }
        return view('character.design.features', [
            'request' => $r,
            'isMyo' => $isMyo,
            'isFreeMyo' => $isFreeMyo,
            'hasSpeciesUsable' => $hasSpeciesUsable,
            'hasSubtypeUsable' => $hasSubtypeUsable,
            'specieses' => $speciesDropdown,
            'subtypes' => $subtypeDropdown,
            'rarities' => ['0' => 'Select Rarity'] + Rarity::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'features' => Feature::orderBy('name')->pluck('name', 'id')->toArray()
        ]);
    }

    /**
     * Shows the edit image subtype portion of the modal
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFeaturesSubtype(Request $request) {

      $species = $request->input('species');
      $id = $request->input('id');
      $r = CharacterDesignUpdate::find($id);
      $isFreeMyo = Character::where('id', $r->character_id)->pluck('is_free_myo')->first();
      $hasSubtypeUsable = Subtype::where('species_id','=',$species)->where('is_free_myo_usable', 1)->count() != 0;
      $noRequiredSubtype = !Settings::get('free_myos_require_subtype');
      if(!$isFreeMyo){
          $subtypeDropdown = ['0' => 'Select Subtype'] + Subtype::where('species_id','=',$species)->orderBy('sort', 'DESC')->pluck('name', 'id')->toArray();
      } else {
          if($hasSubtypeUsable && $noRequiredSubtype){
            $subtypeDropdown = ['0' => 'Select Subtype'] + Subtype::where('species_id','=',$species)->where('is_free_myo_usable', 1)->orderBy('sort', 'DESC')->pluck('name', 'id')->toArray();
          } elseif ($hasSubtypeUsable && !$noRequiredSubtype) {
            $subtypeDropdown = Subtype::where('species_id','=',$species)->where('is_free_myo_usable', 1)->orderBy('sort', 'DESC')->pluck('name', 'id')->toArray();
          } else {
            $subtypeDropdown = ['0' => 'No Subtypes Available'];
          };
      };
      return view('character.design._features_subtype', [
          'subtypes' => $subtypeDropdown,
          'subtype' => $id
      ]);
    }

    /**
     * Edits a design update request's features section.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\CharacterManager  $service
     * @param  int                            $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postFeatures(Request $request, CharacterManager $service, $id)
    {
        $r = CharacterDesignUpdate::find($id);
        if(!$r) abort(404);
        if($r->user_id != Auth::user()->id) abort(404);

        if($service->saveRequestFeatures($request->all(), $r)) {
            flash('Request edited successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Shows the design update request submission confirmation modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getConfirm($id)
    {
        $r = CharacterDesignUpdate::find($id);
        if(!$r || ($r->user_id != Auth::user()->id && !Auth::user()->hasPower('manage_characters'))) abort(404);
        return view('character.design._confirm_modal', [
            'request' => $r
        ]);
    }

    /**
     * Submits a design update request for approval.
     *
     * @param  App\Services\CharacterManager  $service
     * @param  int                            $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSubmit(CharacterManager $service, $id)
    {
        $r = CharacterDesignUpdate::find($id);
        if(!$r) abort(404);
        if($r->user_id != Auth::user()->id) abort(404);

        if($service->submitRequest($r)) {
            flash('Request submitted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Shows the design update request deletion confirmation modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDelete($id)
    {
        $r = CharacterDesignUpdate::find($id);
        if(!$r || ($r->user_id != Auth::user()->id && !Auth::user()->hasPower('manage_characters'))) abort(404);
        return view('character.design._delete_modal', [
            'request' => $r
        ]);
    }

    /**
     * Deletes a design update request.
     *
     * @param  App\Services\CharacterManager  $service
     * @param  int                            $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDelete(CharacterManager $service, $id)
    {
        $r = CharacterDesignUpdate::find($id);
        if(!$r) abort(404);
        if($r->user_id != Auth::user()->id) abort(404);

        if($service->deleteRequest($r)) {
            flash('Request deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('designs');
    }
}
