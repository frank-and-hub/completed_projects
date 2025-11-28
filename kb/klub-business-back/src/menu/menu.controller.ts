import { Controller, Get, Post, Body, Patch, Param, Delete, Query, UseGuards, ParseUUIDPipe } from '@nestjs/common';
import { MenuService } from './menu.service';
import { CreateMenuDto, UpdateMenuDto } from './dto';
import { QueryDto } from 'src/auth/dto';
import { JwtGuard } from 'src/auth/guards';
import { MenuEntity } from './entities';

@UseGuards(JwtGuard)
@Controller({ path: 'menu', version: '1' })
export class MenuController {
  constructor(private readonly menuService: MenuService) { }

  @Post()
  create(@Body() createMenuDto: CreateMenuDto){
    return this.menuService.create(createMenuDto);
  }

  @Get()
  findAll(@Query() query: QueryDto) {
    return this.menuService.findAll(query);
  }

  @Delete()
  removeAll() {
    return this.menuService.removeAll();
  }

  @Get(':id')
  findOne(@Param('id', ParseUUIDPipe) id: string){
    return this.menuService.findOne(id);
  }

  @Patch(':id')
  update(@Param('id', ParseUUIDPipe) id: string, @Body() updateMenuDto: UpdateMenuDto){
    return this.menuService.update(id, updateMenuDto);
  }

  @Delete(':id')
  remove(@Param('id', ParseUUIDPipe) id: string) {
    return this.menuService.remove(id);
  }
}
