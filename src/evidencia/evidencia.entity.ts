import { Entity, PrimaryKey, Property } from '@mikro-orm/core';

@Entity()
export class Evidencia {
   @PrimaryKey()
   cod_Evidencia!: number;

   @Property({ type: 'text' })
    descripcion!: string;
}