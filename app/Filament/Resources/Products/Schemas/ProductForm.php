<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()->components([
                    Section::make('Product information')->components([
                        TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur:true)
                        ->afterStateUpdated(function(string $operation,$state,Set $set){
                            if($operation!=='create'){
                                return;
                            }
                            $set('slug',Str::slug($state));
                        }),
                        TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->disabled()
                        ->dehydrated()
                        ->unique('products','slug',ignoreRecord:true),
                        MarkdownEditor::make('description')
                        ->columnSpanFull()
                        ->fileAttachmentsDirectory('Products')
                    ])->columns(2),

                  Section ::make('Images')->components([
                    FileUpload::make('images')
                    ->multiple()
                    ->directory('Products')
                    ->maxFiles(5)
                    ->reorderable()
                  ])
                ])->columnSpan(2),

              Group::make() ->components([
                Section::make('Price')->components([
                    TextInput::make('price')
                    ->numeric()
                    ->required()
                    ->prefix('BIF')
                ]),
                 Section::make('Associations')->components([
                    Select::make('category_id')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->relationship('category','name'),

                    Select::make('brand_id')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->relationship('brand','name')
                ]),

                 Section::make('Status')->components([
                    Toggle::make('is_stock')
                    ->required()
                    ->default(true),

                    Toggle::make('is_active')
                    ->required()
                    ->default(true),

                    Toggle::make('is_featured')
                    ->required(),
                      Toggle::make('on_sale')
                    ->required()

                ])

              ]),




            ])->columns(3);
    }
}
