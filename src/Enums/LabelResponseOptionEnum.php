<?php

namespace SmartDato\FedEx\Enums;

enum LabelResponseOptionEnum: string
{
    case URL_ONLY = 'URL_ONLY';
    case LABEL = 'LABEL';
}
