<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Address;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Order Information')
                    ->description('Basic order details')
                    ->columnSpan(2)
                    ->schema([
                        Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable(),
                        TextInput::make('order_number')
                            ->required()
                            ->unique(),
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending')
                            ->required(),
                        TextInput::make('total_amount')
                            ->required()
                            ->numeric()
                            ->prefix('$'),
                        Textarea::make('notes')
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
                Section::make('Addresses')
                    ->description('Shipping and billing addresses')
                    ->schema([
                        Select::make('shipping_address_id')
                            ->label('Shipping Address')
                            ->options(Address::all()->pluck('label', 'id'))
                            ->searchable()
                            ->nullable(),
                        Select::make('billing_address_id')
                            ->label('Billing Address')
                            ->options(Address::all()->pluck('label', 'id'))
                            ->searchable()
                            ->nullable(),
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
                                    ->searchable(),
                                TextInput::make('quantity')
                                    ->required()
                                    ->numeric()
                                    ->default(1),
                                TextInput::make('unit_price')
                                    ->required()
                                    ->numeric()
                                    ->prefix('$'),
                                TextInput::make('subtotal')
                                    ->required()
                                    ->numeric()
                                    ->prefix('$'),
                            ]),
                    ]),
            ]);
    }
}
