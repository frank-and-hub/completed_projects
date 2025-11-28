import { Injectable } from '@nestjs/common';
import { CreateFileDto } from './dto/create-file.dto';
import { UpdateFileDto } from './dto/update-file.dto';

@Injectable()
export class FilesService {
  async create(createFileDto: CreateFileDto) {
    return 'This action adds a new file';
  }

  async findAll() {
    return `This action returns all files`;
  }

  async findOne(id: number) {
    return `This action returns a #${id} file`;
  }

  async update(id: number, updateFileDto: UpdateFileDto) {
    return `This action updates a #${id} file`;
  }

  async remove(id: number) {
    return `This action removes a #${id} file`;
  }
}
