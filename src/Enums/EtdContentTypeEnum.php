<?php

namespace SmartDato\FedEx\Enums;

enum EtdContentTypeEnum: string
{
    case PDF = 'application/pdf';
    case X_SOFFICE = 'application/x-soffice';
    case DOC = 'application/doc';
    case TEXT_RICHTEXT = 'text/richtext';
    case TEXT_RTF = 'text/rtf';
    case X_RTF = 'application/x-rtf';
    case RTF = 'application/rtf';
    case MSWORD = 'application/msword';
    case TEXT_PLAIN = 'text/plain';
    case BMP = 'image/bmp';
    case PNG = 'image/png';
    case GIF = 'image/gif';
    case JPEG = 'image/jpeg';
    case TIFF = 'image/tiff';
    case DOCX = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    case XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    case XLS = 'application/vnd.ms-excel';
}
