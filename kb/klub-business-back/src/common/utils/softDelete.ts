export const softDeleteHelpers = {
  // Include soft delete in all queries automatically
  withSoftDelete: {
    deletedAt: null
  },
  
  // Helper methods
  findActive: (prisma: any, model: string, where: any = {}) => {
    return prisma[model].findMany({
      where: { ...where, deletedAt: null }
    })
  },
  
  softDelete: (prisma: any, model: string, id: string) => {
    return prisma[model].update({
      where: { id },
      data: { deletedAt: new Date() }
    })
  },
  
  restore: (prisma: any, model: string, id: string) => {
    return prisma[model].update({
      where: { id },
      data: { deletedAt: null }
    })
  },
  
  findIncludingDeleted: (prisma: any, model: string, where: any = {}) => {
    return prisma[model].findMany({ where })
  }
}