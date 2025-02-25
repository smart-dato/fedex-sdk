<?php

namespace SmartDato\FedEx\Enums;

enum PackagingTypeEnum: string
{
    case YOUR_PACKAGING = 'YOUR_PACKAGING';
    case FEDEX_ENVELOPE = 'FEDEX_ENVELOPE';
    case FEDEX_BOX = 'FEDEX_BOX';
    case FEDEX_SMALL_BOX = 'FEDEX_SMALL_BOX';
    case FEDEX_MEDIUM_BOX = 'FEDEX_MEDIUM_BOX';
    case FEDEX_LARGE_BOX = 'FEDEX_LARGE_BOX';
    case FEDEX_EXTRA_LARGE_BOX = 'FEDEX_EXTRA_LARGE_BOX';
    case FEDEX_10KG_BOX = 'FEDEX_10KG_BOX';
    case FEDEX_25KG_BOX = 'FEDEX_25KG_BOX';
    case FEDEX_PAK = 'FEDEX_PAK';
    case FEDEX_TUBE = 'FEDEX_TUBE';
}
