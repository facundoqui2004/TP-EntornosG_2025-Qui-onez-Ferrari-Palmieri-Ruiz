import { Options } from '@mikro-orm/core';
import { MySqlDriver } from '@mikro-orm/mysql';

const config: Options<MySqlDriver> = {
  dbName: 'tpdsw',
  user: 'root',
  password: 'root',
  host: 'localhost',
  port: 3306,
  driver: MySqlDriver,
  entities: ['./dist/**/*.entity.js'],
  entitiesTs: ['./src/**/*.entity.ts'],
  debug: true,
};

export default config;

