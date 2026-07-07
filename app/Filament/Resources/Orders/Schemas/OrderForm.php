<?php

namespace App\Filament\Resources\Orders\Schemas;

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
                        ->required(),
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
                 ->relationship('shippingAddress', 'label')
                 ->searchable()
                 ->preload()
                 ->nullable()
                 ->createOptionForm([
                     \Filament\Forms\Components\TextInput::make('label')->required(),
                     \Filament\Forms\Components\TextInput::make('street')->required(),
                     \Filament\Forms\Components\TextInput::make('city')->required(),
                     \Filament\Forms\Components\TextInput::make('state'),
                     \Filament\Forms\Components\TextInput::make('zip_code'),
                     \Filament\Forms\Components\TextInput::make('country')->default('BI'),
                 ]),

                 Select::make('billing_address_id')
                 ->label('Billing Address')
                 ->relationship('billingAddress', 'label')
                 ->searchable()
                 ->preload()
                 ->nullable()
                 ->createOptionForm([
                     \Filament\Forms\Components\TextInput::make('label')->required(),
                     \Filament\Forms\Components\TextInput::make('street')->required(),
                     \Filament\Forms\Components\TextInput::make('city')->required(),
                     \Filament\Forms\Components\TextInput::make('state'),
                     \Filament\Forms\Components\TextInput::make('zip_code'),
                     \Filament\Forms\Components\TextInput::make('country')->default('BI'),
                 ]),

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
