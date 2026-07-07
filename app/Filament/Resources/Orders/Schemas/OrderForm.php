<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Order Information')
                    ->columnSpan(2)
                    ->schema([
                        Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable(),
                        TextInput::make('order_number')
                            ->required()
                            ->unique()
                            ->default('ORD-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4))),
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending')
                            ->required(),
                        Textarea::make('notes')
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
                Section::make('Order Items')
                    ->description('Products in this order')
                    ->columnSpan(2)
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Select::make('product_id')
                                    ->label('Product')
                                    ->relationship('product', 'name')
                                    ->required()
                                    ->searchable()
                                    ->columnSpan(3)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $product = Product::find($state);
                                        if ($product) {
                                            $set('unit_price', $product->price);
                                            $quantity = (float) $get('quantity') ?: 1;
                                            $subtotal = $product->price * $quantity;
                                            $set('subtotal', $subtotal);
                                            $items = $get('items');
                                            $total = collect($items)->sum('subtotal');
                                            $set('total_amount', $total);
                                        }
                                    }),
                                TextInput::make('quantity')
                                    ->required()
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->columnSpan(2)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $unitPrice = (float) $get('unit_price');
                                        $subtotal = $state * $unitPrice;
                                        $set('subtotal', $subtotal);
                                        $items = $get('items');
                                        $total = collect($items)->sum('subtotal');
                                        $set('total_amount', $total);
                                    }),
                                TextInput::make('unit_price')
                                    ->required()
                                    ->numeric()
                                    ->prefix('$')
                                    ->columnSpan(2)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $quantity = (float) $get('quantity') ?: 1;
                                        $subtotal = $state * $quantity;
                                        $set('subtotal', $subtotal);
                                        $items = $get('items');
                                        $total = collect($items)->sum('subtotal');
                                        $set('total_amount', $total);
                                    }),
                                TextInput::make('subtotal')
                                    ->required()
                                    ->numeric()
                                    ->prefix('$')
                                    ->columnSpan(2)
                                    ->readonly()
                                    ->dehydrated(),
                            ])
                            ->columns(10)
                            ->addActionLabel('Add Item')
                            ->afterStateUpdated(function ($state, callable $set) {
                                $total = collect($state)->sum('subtotal');
                                $set('total_amount', $total);
                            })
                            ->deleteAction(
                                fn (Action $action) => $action->label(''),
                            ),
                        TextInput::make('total_amount')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->readonly()
                            ->dehydrated(),
                    ]),
            ]);
    }
}
