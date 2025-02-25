<?php

namespace SmartDato\FedEx\Enums;

enum LabelPrintingOrientationEnum: string
{
    case BOTTOM_EDGE_OF_TEXT_FIRST = 'BOTTOM_EDGE_OF_TEXT_FIRST';
    case TOP_EDGE_OF_TEXT_FIRST = 'TOP_EDGE_OF_TEXT_FIRST';
}
