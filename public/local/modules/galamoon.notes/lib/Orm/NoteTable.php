<?php

namespace Galamoon\Notes\Orm;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\Type\DateTime;

class NoteTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'b_galamoon_notes';
    }

    public static function getMap(): array
    {
        return [
            (new IntegerField('ID'))
                ->configurePrimary()
                ->configureAutocomplete(),

            (new IntegerField('USER_ID'))
                ->configureRequired(),

            (new StringField('TITLE'))
                ->configureRequired()
                ->configureSize(255),

            (new TextField('TEXT'))
                ->configureRequired()
                ->configureSize(64000),

            (new DatetimeField('DATE_CREATE'))
                ->configureRequired()
                ->configureDefaultValueNow()
                ->addSaveDataModifier(static function ($value) {
                    if ($value instanceof DateTime) {
                        return $value;
                    }
                    return new DateTime();
                }),

            (new DatetimeField('DATE_MODIFY'))
                ->configureRequired()
                ->configureDefaultValueNow()
                ->addSaveDataModifier(static function () {
                    return new DateTime();
                }),

            (new BooleanField('ACTIVE'))
                ->configureValues('N', 'Y')
                ->configureDefaultValue('Y'),
        ];
    }
}
