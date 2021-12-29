
drop database if exists MEI_TRAB;
create database MEI_TRAB;

use MEI_TRAB;

CREATE TABLE Encomenda ( -- Não está em 3FN!!!
 EncID int NOT NULL CHECK (EncID >= 1),
 ClienteID int NOT NULL CHECK (ClienteID >= 1),
 Nome nvarchar(50) NOT NULL, -- Nome cliente
 Morada nvarchar(30) NOT NULL DEFAULT 'Covilhã', -- Morada cliente
 CONSTRAINT PK_Encomenda PRIMARY KEY (EncID) -- Chave primária
 );

CREATE TABLE EncLinha (
 EncId int NOT NULL,
 ProdutoID int NOT NULL,
 Designacao nvarchar (50) NOT NULL , -- Designação produto
 Preco decimal(10,2) NOT NULL DEFAULT 10.0 CHECK (Preco >= 0.0),
 Qtd decimal(10,2) NOT NULL DEFAULT 1.0 CHECK (Qtd >= 0.0), -- Qtdproduto
 CONSTRAINT PK_EncLinha
 PRIMARY KEY (EncId, ProdutoID), -- constraint type: primary key
 CONSTRAINT FK_EncId FOREIGN KEY (EncId)
 REFERENCES Encomenda(EncId)
 ON UPDATE CASCADE
 ON DELETE NO ACTION
 );

CREATE TABLE LogOperations (
 NumReg int not null auto_increment, -- Auto increment
 EventType char(1), -- I, U, D (Insert, Update, Delete)

 -- Log
 Objecto varchar(30),
 Valor varchar(100),
 Referencia varchar(100),
 -- Dados sobre o utilizador e posto de trabalho
 UserID nvarchar(30) NOT NULL DEFAULT '',
 TerminalD nvarchar(30) NOT NULL DEFAULT '', -- can eventualy be replaced by select group_concat(host) from information_schema.processlist in order to get the ips used or by SELECT SUBSTRING_INDEX(USER(), '@', -1)
 TerminalName nvarchar(30) NOT NULL DEFAULT '',
 -- Quanto ocorreu a operação
 DCriacao datetime NOT NULL DEFAULT now(),
 CONSTRAINT PK_LogOperations PRIMARY KEY (NumReg)
 );


-- Este comportamento teve de ser movido para dentro de cada um dos triggers por uma questao de concorrencia.
-- o mysql nao suporta (?)
drop trigger if exists trg_log_before_insert;

create trigger trg_log_before_insert before insert on LogOperations for each row
    begin
    -- update LogOperations set new.UserID = (select user()), NEW.TerminalD= (select @@hostname), new.TerminalName=( select @@hostname) where NumReg=new.NumReg;
    set new.UserID = (select user());
    set NEW.TerminalD= (select @@hostname);
    set new.TerminalName=( select @@hostname);
    end;


# *********************************************************************
# Stored Procedure
-- -----------------------------------------------------------------------------
-- ---------------------- CREATE SOME Stored Procedure -------------------------
--                            and use them...
-- -----------------------------------------------------------------------------
-- ---------------------------------------------------------------------------
-- USE MEI_TRAB: Changes the database context to the MEI_TRAB database.
--
USE MEI_TRAB;
--
-- -----------------------------------------------------------------------------

drop procedure if exists INSERIR_ENCOMENDA;
delimiter $$
-- Insere uma nova encomenda
CREATE PROCEDURE INSERIR_ENCOMENDA()
BEGIN
  DECLARE i_EncId int default 1;

    -- get next EncId
    Select Max(EncId) into i_EncId From Encomenda;
    IF (i_EncId IS NULL) THEN
      SET i_EncId = 1;
    ELSE
      SET i_EncId = i_EncId +1;
    END IF;
    select i_EncId;
    -- Inserir  encomenda
    INSERT INTO Encomenda (encid, clienteid, nome, morada)
    Values (i_EncId, 1000, 'Fernando Pessoa', 'Lisboa');
    INSERT INTO EncLinha (encid, produtoid, designacao, preco, qtd)
    Values (i_EncId, 111, 'Mensagem', 2500, 2);
    INSERT INTO EncLinha (encid, produtoid, designacao, preco, qtd)
    Values (i_EncId, 131, 'Livro do Desassossego', 3000, 1);
END $$
DELIMITER ;


drop procedure if exists  APAGAR_ENCOMENDA;
delimiter $$
-- Apaga uma encomenda

CREATE PROCEDURE APAGAR_ENCOMENDA()
BEGIN
  DECLARE i_EncId int;
  DECLARE N_Linhas int;
  DECLARE Random , Upper , Lower INT;

  -- Obter a quantidade de encomendas
  Select Count(*) into N_Linhas From Encomenda;

  -- Limites para a geração de números aleatórios
  Set Lower = 1;   -- Menor valor
  Set Upper = N_Linhas; -- Maior valor

  -- Escolher aleatóriamente a linha a apagar
  Set Random = ROUND(((Upper - Lower -1)* RAND() + Lower), 0);

  -- Obter o ID da encomenda na linha "Random"
    select X.encId into i_EncId from  (
      SELECT EncId, (@rownum := @rownum + 1) AS row_number
      FROM Encomenda ,
           (SELECT @rownum := 0) r having row_number= Random) as X ;
    select CONCAT('ENCOMENDA:', i_EncId, '|N_Linhas:', N_Linhas, '|Random:',Random) ;

    Delete From EncLinha Where EncId = i_EncId;
    Delete From Encomenda Where EncId = i_EncId;

END $$;
delimiter ;



drop procedure if exists ACTUALIZAR_ENCOMENDA;
delimiter $$
-- Actualiza uma encomenda
CREATE PROCEDURE ACTUALIZAR_ENCOMENDA()
begin
  DECLARE i_EncId int;
  DECLARE N_Linhas int;
    DECLARE Random, Upper, Lower INT;

  -- Obter a quantidade de encomendas
  Select Count(*) into N_Linhas From Encomenda;



  -- Limites para a geração de números aleatórios
  Set Lower = 1;   -- Menor valor
  Set Upper = N_Linhas; -- Maior valor

  -- Escolher aleatóriamente a linha a encomendar
  Set Random = ROUND(((Upper - Lower -1)* RAND() + Lower), 0);

  -- Obter o ID da encomenda na linha "Random"
    select X.encId into i_EncId from  (
      SELECT t.EncId, @rownum := @rownum + 1 AS row_number
      FROM Encomenda t,
           (SELECT @rownum := 0) r having row_number= @Random) as X ;

    select CONCAT('ENCOMENDA:', i_EncId, '|N_Linhas:', N_Linhas, '|Random:',Random) ;

  -- Alterar o campo Morada para o instante corrente
  Update Encomenda
    Set Morada = current_date
  Where EncId = i_EncId;


end $$

Delimiter ;



drop procedure if exists dowork;
delimiter $$
create procedure dowork()
begin
    DECLARE startTime datetime ;
    DECLARE endTime datetime;
    DECLARE Random, Upper, Lower INT;

    set startTime=now();
    set endTime = date_add(startTime, interval 15 MINUTE);
    -- Limites para a geração de números aleatórios
    Set Lower = 1;   -- Menor valor
    Set Upper = 100; -- Maior valor

    -- Loop until timeout (or BREAK is called)
    WHILE (now() < endTime) DO
        -- Gerar número aleatório entre 1 e 100
        Set Random = ROUND(((Upper - Lower -1)* RAND() + Lower), 0);
        -- Faz uma operação de acordo com o valor do número gerado
        --  40% Insert; 40% Update; 20% Delete
        IF (Random < 40) THEN  -- 40% inserir
         call INSERIR_ENCOMENDA();
        ELSEIF (Random < 80) THEN -- 40%
        call ACTUALIZAR_ENCOMENDA();
        ELSE
        call APAGAR_ENCOMENDA();
        END IF;
        -- Wait 15 seconds
        select sleep(15); -- use select sleep rather than sleep as sleep locks the DB.
    END WHILE;
END $$

delimiter ;

# *********************************************************************


USE MEI_TRAB;

drop trigger if exists TR_Encomenda_I;
delimiter $$
-- Trigger associado a Insert e Update. O Delete de encomendas é tratado à parte.

CREATE TRIGGER TR_Encomenda_I after insert ON Encomenda for each row
begin
      INSERT INTO LogOperations (EventType, Objecto, Valor, Referencia)
      Values ('I', 'Encomenda', new.EncID, '');
end $$
delimiter ;

drop trigger if exists TR_Encomenda_U;
delimiter $$
-- Trigger associado a Insert e Update. O Delete de encomendas é tratado à parte.

CREATE TRIGGER TR_Encomenda_U after update ON Encomenda for each row
begin
      INSERT INTO LogOperations (EventType, Objecto, Valor, Referencia)
      Values ('U', 'Encomenda', new.EncID, '');
end $$
delimiter ;
drop trigger if exists TR_Encomenda_D;
delimiter $$
-- Trigger associado a Insert e Update. O Delete de encomendas é tratado à parte.

CREATE TRIGGER TR_Encomenda_D after delete ON Encomenda for each row
begin
      INSERT INTO LogOperations (EventType, Objecto, Valor, Referencia)
      Values ('D', 'Encomenda', concat(old.EncID,'|',OLD.Nome,'|',OLD.Morada), '');
end $$
delimiter ;

drop trigger if exists TR_EncLinha_I;
delimiter $$
-- Trigger associado a Insert e Update. O Delete de encomendas é tratado à parte.
CREATE TRIGGER TR_EncLinha_I after insert ON EncLinha for each row
begin
      INSERT INTO LogOperations (EventType, Objecto, Valor, Referencia)
      Values ('I', 'EncLinha', new.EncId, new.ProdutoID);
end $$
delimiter ;

drop trigger if exists TR_EncLinha_U;
    delimiter $$
    -- Trigger associado a Insert e Update. O Delete de encomendas é tratado à parte.
    CREATE TRIGGER TR_EncLinha_U after update ON EncLinha for each row
    begin
          INSERT INTO LogOperations (EventType, Objecto, Valor, Referencia)
          Values ('U', 'EncLinha', new.EncId, new.ProdutoID);
    end $$
delimiter ;
drop trigger if exists TR_EncLinha_D;
    delimiter $$
    -- Trigger associado a Insert e Update. O Delete de encomendas é tratado à parte.
    CREATE TRIGGER TR_EncLinha_D after delete ON EncLinha for each row
    begin
          INSERT INTO LogOperations (EventType, Objecto, Valor, Referencia)
          Values ('D', 'EncLinha', concat(OLD.EncId,'|',OLD.ProdutoID) , concat(OLD.Designacao,'|',OLD.Preco,'|',OLD.Qtd) );
    end $$
    delimiter ;


