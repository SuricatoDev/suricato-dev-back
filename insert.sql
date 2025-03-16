-- ARQUIVO PARA ARMAZENAMENTO DE CÓDIGOS SQL, PARA AJUSTES NO BANCO --

ALTER TABLE prjExcursionistas.users MODIFY COLUMN id int(10) unsigned auto_increment NULL;

ALTER TABLE prjExcursionistas.denuncias MODIFY COLUMN denunciante_id int(10) unsigned NOT NULL;
ALTER TABLE prjExcursionistas.denuncias MODIFY COLUMN passageiro_id int(10) unsigned DEFAULT NULL NOT NULL;
ALTER TABLE prjExcursionistas.denuncias MODIFY COLUMN organizador_id int(10) unsigned DEFAULT NULL NOT NULL;
ALTER TABLE prjExcursionistas.denuncias MODIFY COLUMN caravana_id int(10) unsigned DEFAULT NULL NOT NULL;

ALTER TABLE users
DROP COLUMN tipo;

ALTER TABLE users
ADD COLUMN passageiro BOOLEAN DEFAULT FALSE AFTER telefone,
ADD COLUMN organizador BOOLEAN DEFAULT FALSE AFTER passageiro;

ALTER TABLE caravanas DROP FOREIGN KEY caravanas_evento_id_foreign;
ALTER TABLE caravanas DROP INDEX caravanas_evento_id_foreign;

ALTER TABLE caravanas
DROP COLUMN evento_id;

DROP TABLE eventos;

ALTER TABLE caravanas
ADD COLUMN categoria VARCHAR(255) NOT NULL AFTER descricao;
