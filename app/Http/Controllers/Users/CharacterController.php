<?php

namespace App\Http\Controllers\Users;

use Illuminate\Http\Request;

use DB;
use Auth;
use Route;
use Settings;
use App\Models\User\User;
use App\Models\Character\Character;
use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyLog;
use App\Models\User\UserCurrency;
use App\Models\Character\CharacterCurrency;
use App\Models\Character\CharacterTransfer;

use App\Services\CurrencyManager;
use App\Services\CharacterManager;

use App\Http\Controllers\Controller;

class CharacterController extends Controller
{
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
    public function getIndex()
    {
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
        return view('home.create_free_myo', [
            'isMyo' => true,
            'isFreeMyo' => true,
        ]);
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
            'user_id', 'owner_url', 'name',
            'description', 'is_visible', 'is_giftable', 'is_tradeable', 'is_sellable',
            'sale_value', 'transferrable_at', 'use_cropper',
            'x0', 'x1', 'y0', 'y1',
            'designer_id', 'designer_url',
            'artist_id', 'artist_url',
            'species_id', 'subtype_id', 'rarity_id', 'feature_id', 'feature_data',
            'image', 'thumbnail'
        ]);
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
    public function getMyos()
    {
        $slots = Auth::user()->myoSlots()->with('image')->get();

        return view('home.myos', [
            'slots' => $slots,
        ]);
    }

    /**
     * Sorts the user's characters.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\CharacterManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortCharacters(Request $request, CharacterManager $service)
    {
        if ($service->sortCharacters($request->only(['sort']), Auth::user())) {
            flash('Characters sorted successfully.')->success();
            return redirect()->back();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Shows the user's transfers.
     *
     * @param  string  $type
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getTransfers($type = 'incoming')
    {
        $transfers = CharacterTransfer::with('sender.rank')->with('recipient.rank')->with('character.image');
        $user = Auth::user();

        switch($type) {
            case 'incoming':
                $transfers->where('recipient_id', $user->id)->active();
                break;
            case 'outgoing':
                $transfers->where('sender_id', $user->id)->active();
                break;
            case 'completed':
                $transfers->where(function($query) use ($user) {
                    $query->where('recipient_id', $user->id)->orWhere('sender_id', $user->id);
                })->completed();
                break;
        }

        return view('home.character_transfers', [
            'transfers' => $transfers->orderBy('id', 'DESC')->paginate(20),
            'transfersQueue' => Settings::get('open_transfers_queue'),
        ]);
    }

    /**
     * Transfers one of the user's own characters.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\CharacterManager  $service
     * @param  int                            $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postHandleTransfer(Request $request, CharacterManager $service, $id)
    {
        if(!Auth::check()) abort(404);

        $action = $request->get('action');

        if($action == 'Cancel' && $service->cancelTransfer(['transfer_id' => $id], Auth::user())) {
            flash('Transfer cancelled.')->success();
        }
        else if($service->processTransfer($request->only(['action']) + ['transfer_id' => $id], Auth::user())) {
            if(strtolower($action) == 'approve'){
                flash('Transfer ' . strtolower($action) . 'd.')->success();
            }
            else {
                flash('Transfer ' . strtolower($action) . 'ed.')->success();
            }
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }
}
