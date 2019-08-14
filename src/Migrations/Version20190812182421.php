<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190812182421 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE stop_loss (id INT AUTO_INCREMENT NOT NULL, trade_id INT DEFAULT NULL, price NUMERIC(27, 18) NOT NULL, UNIQUE INDEX UNIQ_34C7D316C2D9760 (trade_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE symbol (symbol VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, status VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, base_asset VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, base_asset_precision SMALLINT NOT NULL, quote_asset VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, quote_precision INT NOT NULL, iceberg_allowed TINYINT(1) NOT NULL, is_spot_trading_allowed TINYINT(1) NOT NULL, is_margin_trading_allowed TINYINT(1) NOT NULL, PRIMARY KEY(symbol)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE take_profit (id INT AUTO_INCREMENT NOT NULL, trade_id INT DEFAULT NULL, quantity NUMERIC(27, 18) DEFAULT NULL, percentage NUMERIC(3, 0) DEFAULT NULL, amount_type SMALLINT NOT NULL, INDEX IDX_CC815C49C2D9760 (trade_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE trade (id INT AUTO_INCREMENT NOT NULL, stoploss_id INT DEFAULT NULL, symbol VARCHAR(50) NOT NULL COLLATE utf8mb4_unicode_ci, quantity NUMERIC(27, 18) NOT NULL, UNIQUE INDEX UNIQ_7E1A436637236D48 (stoploss_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE stop_loss');
        $this->addSql('DROP TABLE symbol');
        $this->addSql('DROP TABLE take_profit');
        $this->addSql('DROP TABLE trade');
    }
}
