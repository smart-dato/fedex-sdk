<?php

namespace SmartDato\FedEx\Enums;

enum EtdWorkflowEnum: string
{
    case PRE_SHIPMENT = 'ETDPreshipment';
    case POST_SHIPMENT = 'ETDPostshipment';
}
