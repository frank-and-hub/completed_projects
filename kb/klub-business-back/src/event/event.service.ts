import { Injectable, NotFoundException } from '@nestjs/common';
import { DbService } from 'src/database/db.service';
import { CreateEventDto, UpdateEventDto } from './dto';
import { EventEntity as Event } from './entities/event.entity';
import { Status } from '../common/enums/prisma-enums';

@Injectable()
export class EventService {
  constructor(private readonly db: DbService) { }

  async create(createEventDto: CreateEventDto): Promise<Event> {
    return this.db.event.create({
      data: createEventDto,
      include: { business: true, attendances: true, addresses: true },
    });
  }

  async findAll(): Promise<Event[]> {
    return this.db.event.findMany({
      where: { status: Status.ACTIVE },
      include: { business: true, attendances: true, addresses: true },
    });
  }

  async findOne(id: string): Promise<Event> {
    const event = await this.db.event.findUnique({
      where: { id },
      include: { business: true, attendances: true, addresses: true },
    });

    if (!event) {
      throw new NotFoundException(`Event with ID ${id} not found`);
    }

    return event;
  }

  async findByBusiness(businessId: string): Promise<Event[]> {
    return this.db.event.findMany({
      where: { businessId, status: Status.ACTIVE },
      include: { business: true, attendances: true, addresses: true },
    });
  }

  async findUpcoming(): Promise<Event[]> {
    return this.db.event.findMany({
      where: {
        status: Status.ACTIVE,
        startDate: { gte: new Date() },
      },
      include: { business: true, attendances: true, addresses: true },
      orderBy: { startDate: 'asc' },
    });
  }

  async findPublic(): Promise<Event[]> {
    return this.db.event.findMany({
      where: {
        status: Status.ACTIVE,
        inPublic: true,
        startDate: { gte: new Date() },
      },
      include: { business: true, attendances: true, addresses: true },
      orderBy: { startDate: 'asc' },
    });
  }

  async update(id: string, updateEventDto: UpdateEventDto): Promise<Event> {
    await this.findOne(id); // Check if exists

    return this.db.event.update({
      where: { id },
      data: updateEventDto,
      include: { business: true, attendances: true, addresses: true },
    });
  }

  async remove(id: string): Promise<Event> {
    await this.findOne(id); // Check if exists

    return this.db.event.update({
      where: { id },
      data: { status: 'INACTIVE', deletedAt: new Date() },
    });
  }

  async getAttendees(eventId: string) {
    return this.db.eventAttendance.findMany({
      where: { eventId, status: Status.ACTIVE },
      include: { user: true },
    });
  }

  async attendEvent(eventId: string, userId: string) {
    // Check if already attending
    const existingAttendance = await this.db.eventAttendance.findFirst({
      where: { eventId, userId, status: Status.ACTIVE },
    });

    if (existingAttendance) {
      throw new Error('User is already attending this event');
    }

    return this.db.eventAttendance.create({
      data: { eventId, userId },
      include: { user: true, event: true },
    });
  }

  async cancelAttendance(eventId: string, userId: string) {
    const attendance = await this.db.eventAttendance.findFirst({
      where: { eventId, userId, status: Status.ACTIVE },
    });

    if (!attendance) {
      throw new NotFoundException('Attendance record not found');
    }

    return this.db.eventAttendance.update({
      where: { id: attendance.id },
      data: { status: 'INACTIVE' },
    });
  }
}
