import { PartialType } from '@nestjs/mapped-types';
import { CreateBusinessStaticPageDto } from './create-business-static-page.dto';

export class UpdateBusinessStaticPageDto extends PartialType(CreateBusinessStaticPageDto) {}
