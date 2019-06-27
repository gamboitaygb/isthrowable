<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180824233627 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE like_users');
        $this->addSql('ALTER TABLE like_post ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE like_post ADD CONSTRAINT FK_83FFB0F3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_83FFB0F3A76ED395 ON like_post (user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE like_users (like_id INT NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_A3E78C0EA76ED395 (user_id), INDEX IDX_A3E78C0E859BFA32 (like_id), PRIMARY KEY(like_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE like_users ADD CONSTRAINT FK_A3E78C0E859BFA32 FOREIGN KEY (like_id) REFERENCES like_post (id)');
        $this->addSql('ALTER TABLE like_users ADD CONSTRAINT FK_A3E78C0EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE like_post DROP FOREIGN KEY FK_83FFB0F3A76ED395');
        $this->addSql('DROP INDEX UNIQ_83FFB0F3A76ED395 ON like_post');
        $this->addSql('ALTER TABLE like_post DROP user_id');
    }
}
