import { Injectable, NotFoundException } from '@nestjs/common';
import { DbService } from 'src/database/db.service';
import { CreateVerificationDto } from './dto/create-verification.dto';
import { UpdateVerificationDto } from './dto/update-verification.dto';
import { Verification } from './entities/verification.entity';
import { Status } from '../common/enums/prisma-enums';

@Injectable()
export class VerificationService {
  constructor(private readonly db: DbService) {}

  async create(createVerificationDto: CreateVerificationDto): Promise<Verification> {
    return this.db.verification.create({
      data: createVerificationDto,
    });
  }

  async findAll(): Promise<Verification[]> {
    return this.db.verification.findMany({
      where: { status: Status.ACTIVE },
      include: { user: true },
    });
  }

  async findOne(id: string): Promise<Verification> {
    const verification = await this.db.verification.findUnique({
      where: { id },
      include: { user: true },
    });

    if (!verification) {
      throw new NotFoundException(`Verification with ID ${id} not found`);
    }

    return verification;
  }

  async findByUserId(userId: string): Promise<Verification[]> {
    return this.db.verification.findMany({
      where: { userId, status: Status.ACTIVE },
      include: { user: true },
    });
  }

  async update(id: string, updateVerificationDto: UpdateVerificationDto): Promise<Verification> {
    await this.findOne(id); // Check if exists

    return this.db.verification.update({
      where: { id },
      data: updateVerificationDto,
      include: { user: true },
    });
  }

  async remove(id: string): Promise<Verification> {
    await this.findOne(id); // Check if exists

    return this.db.verification.update({
      where: { id },
      data: { status: 'INACTIVE', deletedAt: new Date() },
    });
  }

  async verifyCode(code: string, userId: string): Promise<Verification> {
    const verification = await this.db.verification.findFirst({
      where: {
        code,
        userId,
        status: Status.ACTIVE,
        expiredAt: { gt: new Date() },
      },
    });

    if (!verification) {
      throw new NotFoundException('Invalid or expired verification code');
    }

    return this.update(verification.id, { verificationStatus: 'SUCCESS' });
  }
}
