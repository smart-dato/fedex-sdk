<?php

namespace SmartDato\FedEx\Enums;

enum LabelOrderEnum: string
{
    case SHIPPING_LABEL_FIRST = 'SHIPPING_LABEL_FIRST';
    case SHIPPING_LABEL_LAST = 'SHIPPING_LABEL_LAST';
}
