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

ALTER TABLE caravanas
DROP COLUMN origem,
DROP COLUMN destino;

ALTER TABLE caravanas
ADD COLUMN endereco_origem VARCHAR(255) AFTER data_retorno,
ADD COLUMN numero_origem VARCHAR(255) NULL AFTER endereco_origem,
ADD COLUMN bairro_origem VARCHAR(255) AFTER numero_origem,
ADD COLUMN cep_origem VARCHAR(255) AFTER bairro_origem,
ADD COLUMN cidade_origem VARCHAR(255) AFTER cep_origem,
ADD COLUMN estado_origem VARCHAR(255) AFTER cidade_origem,
ADD COLUMN endereco_destino VARCHAR(255) AFTER estado_origem,
ADD COLUMN numero_destino VARCHAR(255) NULL AFTER endereco_destino,
ADD COLUMN bairro_destino VARCHAR(255) AFTER numero_destino,
ADD COLUMN cep_destino VARCHAR(255) AFTER bairro_destino,
ADD COLUMN cidade_destino VARCHAR(255) AFTER cep_destino,
ADD COLUMN estado_destino VARCHAR(255) AFTER cidade_destino;

ALTER TABLE users
DROP COLUMN endereco,
DROP COLUMN numero,
DROP COLUMN bairro,
DROP COLUMN cep,
DROP COLUMN cidade,
DROP COLUMN estado;

ALTER TABLE passageiros
ADD COLUMN endereco VARCHAR(255) NOT NULL AFTER contato_emergencia,
ADD COLUMN numero VARCHAR(255) NOT NULL AFTER endereco,
ADD COLUMN complemento VARCHAR(255) NOT NULL AFTER numero,
ADD COLUMN bairro VARCHAR(255) NOT NULL AFTER complemento,
ADD COLUMN cep VARCHAR(255) NOT NULL AFTER bairro,
ADD COLUMN cidade VARCHAR(255) NOT NULL AFTER cep,
ADD COLUMN estado VARCHAR(255) NOT NULL AFTER cidade;

ALTER TABLE organizadores
ADD COLUMN nome_fantasia VARCHAR(255) NULL AFTER razao_social,
ADD COLUMN telefone_comercial VARCHAR(255) NOT NULL AFTER inscricao_municipal;
ADD COLUMN endereco VARCHAR(255) NOT NULL AFTER telefone_comercial,
ADD COLUMN numero VARCHAR(255) NOT NULL AFTER endereco,
ADD COLUMN complemento VARCHAR(255) NOT NULL AFTER numero,
ADD COLUMN bairro VARCHAR(255) NOT NULL AFTER complemento,
ADD COLUMN cep VARCHAR(255) NOT NULL AFTER bairro,
ADD COLUMN cidade VARCHAR(255) NOT NULL AFTER cep,
ADD COLUMN estado VARCHAR(255) NOT NULL AFTER cidade;

ALTER TABLE carava_imagens
ADD COLUMN ordem INT NULL AFTER path;

ALTER TABLE caravanas
ADD COLUMN vagas_disponiveis INT NOT NULL
AFTER numero_vagas;



