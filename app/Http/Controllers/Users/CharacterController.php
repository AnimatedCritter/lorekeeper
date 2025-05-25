<?php

namespace App\Http\Controllers\Users;

use App\Facades\Settings;
use App\Http\Controllers\Controller;
use App\Models\Character\Character;
use App\Models\Character\CharacterDesignUpdate;
use App\Models\Character\CharacterTransfer;
use App\Models\Species\Species;
use App\Models\Species\Subtype;
use App\Models\User\User;
use App\Services\CharacterManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CharacterController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Character Controller
    |--------------------------------------------------------------------------
    |
    | Handles displaying of the user's characters and transfers.
    |
    */

    /**
     * Shows the user's characters.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        $characters = Auth::user()->characters()->with('image')->visible()->whereNull('trade_id')->get();

        return view('home.characters', [
            'characters' => $characters,
        ]);
    }

    /**
     * Shows the create free MYO slot page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateFreeMyo()
    {
        $closed = !Settings::get('free_myos_open');
        $hasMaxNumber = config('lorekeeper.free_myos.free_myos_max_number') != 0;
        $maxNumber = config('lorekeeper.free_myos.free_myos_max_number');

        // get available species and subtypes
        $hasSpeciesUsable = Species::visible()->where('is_free_myo_usable', 1)->count() != 0;
        $hasSubtypeUsable = Subtype::visible()->where('is_free_myo_usable', 1)->count() != 0;
        $requireSubtype = config('lorekeeper.free_myos.free_myos_require_subtype');
        $inactiveMyoId = Character::visible()->where('user_id', Auth::user()->id)->where('is_myo_slot', 1)->where('is_free_myo', 1)->pluck('id');
        $listInactiveMyos = array();
        foreach($inactiveMyoId as $myoId) {
         $listInactiveMyos[] = CharacterDesignUpdate::where('status', '!=', 'Cancelled')->where('character_id', $myoId)->value('id');
        }
        $hasInactiveMyo = in_array(null, $listInactiveMyos);
        
        return view('home.create_free_myo', [
            'specieses' => ['0' => 'Select Species'] + Species::visible()->where('is_free_myo_usable', 1)->orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'subtypes' => ['0' => 'Pick a Species First'],

            'closed' => $closed,
            'hasMaxNumber' => $hasMaxNumber,
            'maxNumber' => $maxNumber,
            'slotName'  => config('lorekeeper.free_myos.myo_slot_name'),

            'hasSpeciesUsable' => $hasSpeciesUsable,
            'hasSubtypeUsable' => $hasSubtypeUsable,
            'requireSubtype' => $requireSubtype,
            'inactiveMyoId' => $inactiveMyoId,
            'hasInactiveMyo' => $hasInactiveMyo,
            'isMyo' => true,
        ]);
    }

    /**
     * Shows the edit image subtype portion of the modal
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateCharacterMyoSubtype(Request $request) {
      $species = $request->input('species');
      $hasSubtypeUsable = Subtype::visible()->where('species_id','=',$species)->where('is_free_myo_usable', 1)->count() != 0;
      $requireSubtype = config('lorekeeper.free_myos.free_myos_require_subtype');

      // select subtype dropdown options
      if($hasSubtypeUsable && !$requireSubtype){
        $subtypeDropdown = ['0' => 'Select Subtype'] + Subtype::visible()->where('species_id','=',$species)->where('is_free_myo_usable', 1)->orderBy('sort', 'DESC')->pluck('name', 'id')->toArray();
      } elseif ($hasSubtypeUsable && $requireSubtype) {
        $subtypeDropdown = Subtype::visible()->where('species_id','=',$species)->where('is_free_myo_usable', 1)->orderBy('sort', 'DESC')->pluck('name', 'id')->toArray();
      } else {
        $subtypeDropdown = ['0' => 'No Subtypes Available'];
      };

      return view('home._create_character_subtype', [
          'subtypes' => $subtypeDropdown,
          'isMyo' => $request->input('myo')
      ]);
    }

    /**
     * Returns the image url of the selected free MYO species/subtype
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getMyoPreviewImage(Request $request) {
        if ($request->input('subtype') != 0) {
            $imageUrl = Subtype::visible()->where('id', $request->input('subtype'))->first()->subtypeImageUrl;
        } elseif ($request->input('species') != 0) {
            $imageUrl = Species::visible()->where('id', $request->input('species'))->first()->speciesImageUrl;
        } else {
            $imageUrl = asset('images/myo.png');
        }

        return $imageUrl;
    }

    /**
     * Creates a free MYO slot.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\CharacterManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateFreeMyo(Request $request, CharacterManager $service)
    {
        $request->validate(Character::$myoRules);
        $data = $request->only([
            'name', 'species_id', 'subtype_id',
        ]) + [
            'user_id' => Auth::user()->id,
            'description' => null,
            'designer_id' => [],
            'designer_url' => [],
            'artist_id' => [],
            'artist_url' => [],
            'feature_id' => [],
            'is_giftable' => config('lorekeeper.free_myos.free_myos_is_giftable'),
            'is_tradeable' => config('lorekeeper.free_myos.free_myos_is_tradeable'),
            'is_sellable' => config('lorekeeper.free_myos.free_myos_is_resellable'),
            'rarity_id' => config('lorekeeper.free_myos.free_myos_rarity'),
            'is_visible' => 1,
        ];

        if ($character = $service->createCharacter($data, Auth::user(), true, true)) {
            flash('MYO slot created successfully.')->success();
            return redirect()->to($character->url.'/approval');
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back()->withInput();
    }

    /**
     * Shows the user's MYO slots.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getMyos() {
        $slots = Auth::user()->myoSlots()->with('image')->get();

        return view('home.myos', [
            'slots' => $slots,
        ]);
    }

    /**
     * Sorts the user's characters.
     *
     * @param App\Services\CharacterManager $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortCharacters(Request $request, CharacterManager $service) {
        if ($service->sortCharacters($request->only(['sort']), Auth::user())) {
            flash('Characters sorted successfully.')->success();

            return redirect()->back();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Shows the user's transfers.
     *
     * @param string $type
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getTransfers($type = 'incoming') {
        $transfers = CharacterTransfer::with('sender.rank')->with('recipient.rank')->with('character.image');
        $user = Auth::user();

        switch ($type) {
            case 'incoming':
                $transfers->where('recipient_id', $user->id)->active();
                break;
            case 'outgoing':
                $transfers->where('sender_id', $user->id)->active();
                break;
            case 'completed':
                $transfers->where(function ($query) use ($user) {
                    $query->where('recipient_id', $user->id)->orWhere('sender_id', $user->id);
                })->completed();
                break;
        }

        return view('home.character_transfers', [
            'transfers'      => $transfers->orderBy('id', 'DESC')->paginate(20),
            'transfersQueue' => Settings::get('open_transfers_queue'),
        ]);
    }

    /**
     * Transfers one of the user's own characters.
     *
     * @param App\Services\CharacterManager $service
     * @param int                           $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postHandleTransfer(Request $request, CharacterManager $service, $id) {
        if (!Auth::check()) {
            abort(404);
        }

        $action = $request->get('action');

        if ($action == 'Cancel' && $service->cancelTransfer(['transfer_id' => $id], Auth::user())) {
            flash('Transfer cancelled.')->success();
        } elseif ($service->processTransfer($request->only(['action']) + ['transfer_id' => $id], Auth::user())) {
            if (strtolower($action) == 'approve') {
                flash('Transfer '.strtolower($action).'d.')->success();
            } else {
                flash('Transfer '.strtolower($action).'ed.')->success();
            }
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
