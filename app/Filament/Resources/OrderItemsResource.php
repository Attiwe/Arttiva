<?php

    namespace App\Filament\Resources;

    use App\Filament\Resources\OrderItemsResource\Pages;
    use App\Models\Order;
    use App\Models\OrderItems;
    use Filament\Forms\Form;
    use Filament\Resources\Resource;
    use Filament\Tables;
    use Filament\Tables\Table;
    use Filament\Forms\Components\Select;
    use Filament\Forms\Components\TextInput;
    use Filament\Forms\Components\Placeholder;
     use Filament\Forms\Components\Hidden;
    use Filament\Tables\Columns\TextColumn;
    use Filament\Tables\Actions\BulkActionGroup;
    use Filament\Tables\Actions\DeleteBulkAction;
    use Barryvdh\DomPDF\Facade\Pdf;
    use Filament\Tables\Actions\Action;
    use Illuminate\Support\Number;
    class OrderItemsResource extends Resource
    {
        protected static ?string $model = OrderItems::class;
        protected static ?int $navigationSort = 2;

        protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

        public static function getNavigationGroup(): ?string
        {
            return app()->getLocale() === 'ar' ? "الطلبات" : "Orders";
        }

        public static function canCreate(): bool
        {
            return false;
        }

        public static function form(Form $form): Form
        {
            return $form
                ->schema([
                    Select::make('order_id')
                        ->label('صاحب الطلب')
                        ->options(fn() => Order::query()
                            ->select(['id', 'first_name', 'last_name'])
                            ->get()
                            ->mapWithKeys(fn($order) => [$order->id => $order->first_name . ' ' . $order->last_name])
                        )
                        ->required()
                        ->default(null),

                    Select::make('product_id')
                        ->relationship('product', 'name')
                        ->searchable(),

                    TextInput::make('quantity')
                        ->numeric()
                        ->minValue(1)
                        ->default(1)
                        ->required()
                        ->columnSpan(3)
                        ->reactive()
                        ->afterStateUpdated(fn($state, $set, $get) => $set('total_amount', $state * $get('unit_amount'))),

                    TextInput::make('unit_amount')
                        ->numeric()
                        ->required()
                        ->disabled()
                        ->dehydrated()
                        ->columnSpan(3),

                    TextInput::make('total_amount')
                        ->numeric()
                        ->dehydrated()
                        ->required()
                        ->columnSpan(2),

                    Placeholder::make('grand_total_placeholder')
                        ->label('Money Totals')
                        ->content(function ($get, $set) {
                            $total = 0;
                            if (!$items = $get('items')) {
                                return $total;
                            }
                            foreach ($items as $key => $repeater) {
                                $total += $get("items.{$key}.total_amount");
                            }
                            $set('grand_total', $total);
                            return Number::currency($total, 'EGP');
                        }),

                    Hidden::make('grand_total')
                        ->default(0),
                ]);
        }

        public static function table(Table $table): Table
        {
            return $table
                ->columns([
                    TextColumn::make('#')->rowIndex(),

                    Tables\Columns\TextColumn::make('order.full_name')
                        ->label('صاحب الطلب')
                        ->getStateUsing(fn($record) => $record->order->first_name . ' ' . $record->order->last_name)
                        ->sortable()
                        ->searchable(),

                    TextColumn::make('product.name')
                        ->label(' اسم المنتح')
                        ->sortable(),

                    TextColumn::make('quantity')
                        ->label('الكمية')
                        ->sortable(),

                    TextColumn::make('unit_amount')
                        ->label('المبلغ لكل  ')
                        ->money('EGP')
                        ->sortable(),

                    TextColumn::make('total_amount')
                        ->label('المبلغ الإجمالي')
                        ->money('EGP')
                        ->sortable(),
                ])
                ->defaultSort('created_at', 'desc')

                ->filters([

                ])
                ->actions([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),

                ])

                ->bulkActions([
                    BulkActionGroup::make([
                        DeleteBulkAction::make(),
                    ]),
                ]);
        }

        public static function getPages(): array
        {
            return [
                'index' => Pages\ListOrderItems::route('/'),
                'edit' => Pages\EditOrderItems::route('/{record}/edit'),
            ];
        }

        public static function getPluralLabel(): string
        {
            return app()->getLocale() == 'ar' ? 'عناصر الطلب ' : 'OrderItems';
        }
     

        public static function getNavigationBadge(): ?string
        {
            return static::getModel()::count();
        }
    
        public static function getNavigationBadgeColor(): string|array|null
        {
            return static::getModel()::count() > 150 ? 'warning' : 'danger';
        }
    
    }
