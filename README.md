# Portal Vale Cultura

É o sistema de gestão do Vale-Cultura, que é um benefício ligado ao Programa de Cultura do Trabalhador, cujo objetivo é garantir acesso e incentivo
 aos programas culturais brasileiros.

## Instruções de instalação

1) Baixe uma cópia do repositório:
    ```
    git clone https://github.com/culturagovbr/portal-vale-cultura.git

2) Defina os arquivos de configuração

    ```
    cd [base_dir]/projeto/application/configs 
    cp application.ini-example application.ini
    cp db.ini-example db.ini 
    

3) Configure o .htaccess na pasta public

    ```
    cd [base_dir]/public
    cp .htaccess-example .htaccess
    ```
   ##### abra o arquivo .htaccess e escolha qual o ambiente. 
   ``` 
   Ex: SetEnv APPLICATION_ENV homologacao

4) Crie e dê permissões de escrita às pastas necessárias:
    ```
    mkdir [base_dir]/public/imagens/captcha
    chmod 775 [base_dir]/public/imagens/captcha

5) Crie ou dê acesso de escrita à pasta dos arquivos enviados ao vale-cultura

    ##### definido atualmente como: /var/arquivos/arquivos-valecultura
    ```
    mkdir /var/arquivos/arquivos-valecultura
    chmod 775 /var/arquivos/arquivos-valecultura
