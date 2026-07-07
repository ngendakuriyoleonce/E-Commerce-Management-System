<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Address;
use App\Models\Product;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Number;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()->components([
                    Section::make('Order Information')->components([
                        Hidden::make('order_number')
                        ->default('ORD-' . now()->format('YmdHis') . '-' . strtoupper(\Illuminate\Support\Str::random(4))),
                        Select::make('user_id')
                        ->label('Customer')
                        ->relationship('user','name')
                        ->preload()
                        ->searchable()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $set('shipping_address_id', null);
                            $set('billing_address_id', null);
                        }),
                    Select::make('payment_method')
                    ->options([
                        'Stripe'=>'Stripe',
                        'Cod'=>'Cash on delivary'
                    ])
                    ->required(),

 Select::make('payment_status')
                    ->options([
                        'Pending'=>'Pending',
                        'Paid'=>'Paid',
                        'Failed'=>'Failed'
                    ])
                    ->default('Pending')
                    ->required(),

                    ToggleButtons::make('status')
                    ->inline()
                    ->default('new')
                    ->required()
                    ->options([
                        'new'=>'new',
                        'processing'=>'processing',
                        'shipped'=>'shipped',
                        'delivered'=>'delivered',
                        'cancelled'=>'cancelled'
                    ])
                    ->colors([
                        'new' => 'info',
                        'processing' => 'warning',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                    ])
                    ->icons([
                         'new' => Heroicon::OutlinedSparkles,
                         'processing' => Heroicon::OutlinedCog6Tooth,
                         'shipped' => Heroicon::OutlinedTruck,
                         'delivered' => Heroicon::OutlinedCheckBadge,
                          'cancelled' => Heroicon::OutlinedXCircle,
                    ]),
                 Select::make('currency')
                 ->options([
                    'BIF'=>'BIF',
                    'USD'=>'USD',
                    'EURO'=>'EURO',
                    'AED'=>'AED'
                 ])
                 ->default('BIF')
                 ->required(),

                  Select::make('shipping_method')
                 ->options([
                   'standard' => 'Standard',
                  'express' => 'Express',
                  'overnight' => 'Overnight',
                  'international' => 'International',
                   'pickup' => 'Local Pickup',
                 ])
                 ->default('standard')
                 ->required(),

                  Select::make('shipping_address_id')
                 ->label('Shipping Address')
                 ->options(fn (Get $get) => Address::where('user_id', $get('user_id'))
                     ->get()
                     ->mapWithKeys(fn ($address) => [$address->id => $address->first_name . ' ' . $address->last_name . ' - ' . $address->street_address])
                 )
                 ->searchable()
                 ->preload()
                 ->nullable()
                 ->reactive()
                 ->hintAction(
                     \Filament\Actions\Action::make('new_shipping')
                         ->icon(Heroicon::OutlinedPlusCircle)
                         ->modalHeading('New Shipping Address')
                         ->modalSubmitActionLabel('Create')
                         ->form([
                             \Filament\Schemas\Components\Grid::make(2)->schema([
                                 TextInput::make('first_name')->required()->maxLength(255),
                                 TextInput::make('last_name')->required()->maxLength(255),
                                 TextInput::make('phone')->required()->tel()->maxLength(255),
                                 Textarea::make('street_address')->required()->columnSpan(2),
                                 TextInput::make('city')->required()->maxLength(255),
                                 TextInput::make('state')->required()->maxLength(255),
                                 TextInput::make('zip_code')->required()->numeric()->maxLength(10),
                             ]),
                         ])
                         ->action(function (array $data, $livewire) {
                             $data['country'] = 'BI';
                             $data['user_id'] = $livewire->data['user_id'] ?? auth()->id();
                             $address = Address::create($data);
                             $livewire->data['shipping_address_id'] = $address->id;
                         }),
                 ),

                 Select::make('billing_address_id')
                 ->label('Billing Address')
                 ->options(fn (Get $get) => Address::where('user_id', $get('user_id'))
                     ->get()
                     ->mapWithKeys(fn ($address) => [$address->id => $address->first_name . ' ' . $address->last_name . ' - ' . $address->street_address])
                 )
                 ->searchable()
                 ->preload()
                 ->nullable()
                 ->reactive()
                 ->hintAction(
                     \Filament\Actions\Action::make('new_billing')
                         ->icon(Heroicon::OutlinedPlusCircle)
                         ->modalHeading('New Billing Address')
                         ->modalSubmitActionLabel('Create')
                         ->form([
                             \Filament\Schemas\Components\Grid::make(2)->schema([
                                 TextInput::make('first_name')->required()->maxLength(255),
                                 TextInput::make('last_name')->required()->maxLength(255),
                                 TextInput::make('phone')->required()->tel()->maxLength(255),
                                 Textarea::make('street_address')->required()->columnSpan(2),
                                 TextInput::make('city')->required()->maxLength(255),
                                 TextInput::make('state')->required()->maxLength(255),
                                 TextInput::make('zip_code')->required()->numeric()->maxLength(10),
                             ]),
                         ])
                         ->action(function (array $data, $livewire) {
                             $data['country'] = 'BI';
                             $data['user_id'] = $livewire->data['user_id'] ?? auth()->id();
                             $address = Address::create($data);
                             $livewire->data['billing_address_id'] = $address->id;
                         }),
                 ),

                 Textarea::make('notes')
                 ->columnSpanFull()

                    ])->columns(2),

              Section::make('Order Items')
             ->schema([

         Repeater::make('items')
            ->relationship()
            ->columns(12)
            ->reactive()
            ->afterStateUpdated(function ($state, Set $set) {
                $total = collect($state)->sum('total_amount');
                $set('total_amount', $total);
            })
            ->schema([

                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->distinct()
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                    ->columnSpan(4)
                    ->reactive()
                    ->afterStateUpdated(function ($state, Set $set) {
                        $price = Product::find($state)?->price ?? 0;

                        $set('unit_amount', $price);
                        $set('total_amount', $price);
                    }),

                TextInput::make('quantity')
                    ->numeric()
                    ->required()
                    ->default(1)
                    ->minValue(1)
                    ->columnSpan(2)
                    ->lazy()
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        $set('total_amount', $state * $get('unit_amount'));
                    }),

                TextInput::make('unit_amount')
                    ->numeric()
                    ->required()
                    ->disabled()
                    ->dehydrated()
                    ->columnSpan(3)
                    ->lazy()
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        $set('total_amount', $state * $get('quantity'));
                    }),

                TextInput::make('total_amount')
                    ->numeric()
                    ->disabled()
                    ->dehydrated()
                    ->columnSpan(3),
            ]),

         Placeholder::make('total_amount_placeholder')
            ->label('Total Amount')
            ->content(function(Get $get ,Set $set){
                $total=0;
                if(!$repeaters=$get('items')){
                    return $total;
                }
                foreach ($repeaters as $key => $repeater) {
                    $total+= $get("items.{$key}.total_amount");
                }
                $set('total_amount',$total);
                return Number::currency($total,'BIF');
            }),
Hidden::make('total_amount')
->default(0)
    ])
                ])->columnSpanFull()
            ]);
    }
}
