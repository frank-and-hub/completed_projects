import {
  Controller,
  Get,
  Post,
  Body,
  Patch,
  Param,
  Delete,
  UseGuards,
  Query,
} from '@nestjs/common';
import { EventService } from './event.service';
import { CreateEventDto } from './dto/create-event.dto';
import { UpdateEventDto } from './dto/update-event.dto';
import { JwtAuthGuard, BusinessOwnerGuard } from 'src/auth/guards';

@Controller({ path: 'event', version: '1' })
@UseGuards(JwtAuthGuard)
export class EventController {
  constructor(private readonly eventService: EventService) {}

  @Post()
  @UseGuards(BusinessOwnerGuard)
  create(@Body() createEventDto: CreateEventDto) {
    return this.eventService.create(createEventDto);
  }

  @Get()
  findAll(
    @Query('businessId') businessId?: string,
    @Query('upcoming') upcoming?: string,
    // @Query('public') public?: string,
  ) {
    if (businessId) {
      return this.eventService.findByBusiness(businessId);
    }
    if (upcoming === 'true') {
      return this.eventService.findUpcoming();
    }
    // if (public === 'true') {
    //   return this.eventService.findPublic();
    // }
    return this.eventService.findAll();
  }

  @Get(':id')
  findOne(@Param('id') id: string) {
    return this.eventService.findOne(id);
  }

  @Get(':id/attendees')
  getAttendees(@Param('id') id: string) {
    return this.eventService.getAttendees(id);
  }

  @Post(':id/attend')
  attendEvent(@Param('id') id: string, @Body() body: { userId: string }) {
    return this.eventService.attendEvent(id, body.userId);
  }

  @Post(':id/cancel-attendance')
  cancelAttendance(@Param('id') id: string, @Body() body: { userId: string }) {
    return this.eventService.cancelAttendance(id, body.userId);
  }

  @Patch(':id')
  @UseGuards(BusinessOwnerGuard)
  update(@Param('id') id: string, @Body() updateEventDto: UpdateEventDto) {
    return this.eventService.update(id, updateEventDto);
  }

  @Delete(':id')
  @UseGuards(BusinessOwnerGuard)
  remove(@Param('id') id: string) {
    return this.eventService.remove(id);
  }
}
