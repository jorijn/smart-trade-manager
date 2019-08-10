<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190810082140 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE take_profit (id INT AUTO_INCREMENT NOT NULL, trade_id INT DEFAULT NULL, quantity NUMERIC(27, 18) DEFAULT NULL, percentage NUMERIC(3, 0) DEFAULT NULL, amount_type SMALLINT NOT NULL, INDEX IDX_CC815C49C2D9760 (trade_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE trade (id INT AUTO_INCREMENT NOT NULL, stoploss_id INT DEFAULT NULL, symbol VARCHAR(50) NOT NULL, quantity NUMERIC(27, 18) NOT NULL, UNIQUE INDEX UNIQ_7E1A436637236D48 (stoploss_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stop_loss (id INT AUTO_INCREMENT NOT NULL, trade_id INT DEFAULT NULL, price NUMERIC(27, 18) NOT NULL, UNIQUE INDEX UNIQ_34C7D316C2D9760 (trade_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE take_profit ADD CONSTRAINT FK_CC815C49C2D9760 FOREIGN KEY (trade_id) REFERENCES trade (id)');
        $this->addSql('ALTER TABLE trade ADD CONSTRAINT FK_7E1A436637236D48 FOREIGN KEY (stoploss_id) REFERENCES stop_loss (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE stop_loss ADD CONSTRAINT FK_34C7D316C2D9760 FOREIGN KEY (trade_id) REFERENCES trade (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE take_profit DROP FOREIGN KEY FK_CC815C49C2D9760');
        $this->addSql('ALTER TABLE stop_loss DROP FOREIGN KEY FK_34C7D316C2D9760');
        $this->addSql('ALTER TABLE trade DROP FOREIGN KEY FK_7E1A436637236D48');
        $this->addSql('DROP TABLE take_profit');
        $this->addSql('DROP TABLE trade');
        $this->addSql('DROP TABLE stop_loss');
    }
}
