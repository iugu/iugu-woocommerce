# WooCommerce iugu #
**Contributors:** iugu, claudiosanches, braising  
**Tags:** woocommerce, iugu, payment  
**Requires at least:** 3.9  
**Tested up to:** 4.9
**Stable tag:** 1.0.11
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

Receba pagamentos por cartão de crédito e boleto bancário na sua loja WooCommerce com a iugu.

## Descrição ##

A iugu disponibiliza toda a infraestrutura necessária para que você possa transacionar pagamentos online com menos burocracia e mais vantagens. Com a nossa plataforma, você pode oferecer pagamentos com checkout transparente com cartão de crédito e boleto bancário.

Saiba mais como a iugu funciona em [iugu - Entendendo como tudo funciona](https://docs.iugu.com).

### Compatibilidade ###

Compatível desde a versão 2.1.x até a 3.2.5 do WooCommerce.

Este plugin funciona integrado com o [WooCommerce Extra Checkout Fields for Brazil](http://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/), que permite enviar dados do cliente como **CPF** ou **CNPJ**, além dos campos **número** e **bairro** do endereço.

Ele também é compatível com os seguintes plugins:

* [WooCommerce Subscriptions](http://www.woothemes.com/products/woocommerce-subscriptions/) - Para pagamentos recorrentes/assinaturas.
* [WooCommerce Pre-orders](http://www.woothemes.com/products/woocommerce-pre-orders/) - Para pré-venda de produtos.

### Instalação ###

Confira o nosso guia de instalação e configuração da iugu na aba [Installation](http://wordpress.org/extend/plugins/iugu-woocommerce/installation/).

### Dúvidas? ###

Você pode esclarecer suas dúvidas usando:

* Nossa sessão de [FAQ](http://wordpress.org/extend/plugins/iugu-woocommerce/faq/).
* Criando um tópico no [fórum público do WordPress](http://wordpress.org/support/plugin/iugu-woocommerce).
* Criando um tópico no [fórum do Github](https://github.com/iugu/iugu-woocommerce/issues).

### Colaborar ###

Você pode contribuir para o plug-in fazendo o fork do repositório no [GitHub](https://github.com/iugu/iugu-woocommerce).

## Installation ##

### Instalação do plugin: ###

* Envie os arquivos do plugin para a pasta `wp-content/plugins` ou instale-o usando o instalador de plugins do WordPress.
* Ative o plugin.

### Requerimentos: ###

- Conta ativa na [iugu](https://iugu.com) com boleto bancário e/ou cartão de crédito aprovados e habilitados como métodos de pagamento. Ver [_O que é necessário para começar a usar iugu?_](https://support.iugu.com/hc/pt-br/articles/201531709).
- Plugins instalados: [WooCommerce](http://wordpress.org/extend/plugins/woocommerce/) e [WooCommerce Extra Checkout Fields for Brazil](http://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/).

### Configuração no WooCommerce ###

No men _WooCommerce > Configurações > Produtos > Inventário_, desabilite (deixe em branco) a opção **Manter estoque (minutos)**, que permite permite cancelar a compra e liberar o estoque depois de alguns minutos.

Essa funcionalidade foi introduzida na versão 2.0 do Woocommerce, mas não funciona muito bem com pagamentos por boleto bancário, pois podem demorar até 48 horas para serem validados.

### Configuração na iugu ###

No menu [_Administração > Configurações da conta_](https://app.iugu.com/account), crie um _API token_ do tipo LIVE. Ele será usado, junto com o ID da sua conta iugu, para configurar o plugin. Você também pode criar um API token do tipo TEST para realizar testes com o plugin.

### Configurações do plugin: ###

Com o plugin instalado, acesse o painel de administração do WordPress e entre em _WooCommerce > Configurações > Finalizar compra_. Selecione **iugu - Cartão de crédito** ou **iugu - Boleto bancário** para ativar o(s) método(s) de pagamento que lhe interessa(m). Marque a caixa de seleção para ativá-lo(s) e preencha as opções de **ID da conta** e **API Token**.

### Configurações de cartão de crédito ###

Entre as opções do cartão de crédito é possível configurar o número de parcelas que os clientes poderam dividir, esta opção não pode ser maior do que o valor configurado dentro da sua conta da iugu.

É possível também manipular a exibição do valor das parcelas com as opções de "Repassar juros", "Sem juros" e "Taxa por transação".

Note que estas opções são apenas de exibição e não configura de nenhuma forma o valor total que o cliente realmente irá pagar, pois o que controla isto são as configurações de parcelas da sua conta da iugu e desta forma as opções de "Repassar juros", "Sem juros", "Taxa por transação" devem represetar exatamente o que você configurou na sua conta da iugu.

Para saber mais sobre parcelamento e juros na iugu leia o tutorial: [Existem juros no parcelamento?](http://support.iugu.com/hc/pt-br/articles/201728767-Existem-juros-no-parcelamento-).

Pronto, sua loja já pode receber pagamentos pela iugu.

### Sandbox ###

É possível também trabalhar com o plugin no modo sandbox, desta forma você pode testar pagamentos antes de realmente utilizar o plugin em produção.

Para utilizar em modo Sandbox você deve criar uma **API Token** do tipo _TEST_ dentro da [sua conta na iugu](https://app.iugu.com/account) e ativar a opção "Sandbox da iugu" no plugin.

Quando for trocar para produção você deve desmarcar a opção e adicionar uma **API Token** do tipo _LIVE_.


## Perguntas frenquentes ##

### Qual é a licença do plugin? ###

Este plugin está licenciado como GPL.

### Do que preciso para utilizar o plugin? ###

* WooCommerce versão 2.1 ou superior.
* Conta ativada na [iugu](https://iugu.com/).
* Pegar o seu **ID de conta** e gerar uma **API Token** na página de [sua conta na iugu](https://app.iugu.com/account).
* Desativar a opção **Manter Estoque (minutos)** do WooCommerce.

Note que você NÃO PRECISA configurar qualquer GATILHO dentro da sua conta da iugu!

### Quais são as tarifas da iugu? ###

Conheça todas as tarifas da iugu em [iugu.com/precos](https://iugu.com/precos/).

### É possível utilizar a opção de pagamento recorrente/assinaturas? ###

Sim, é possível utilizar este plugin para fazer pagamento recorrente integrado com o [WooCommerce Subscriptions](http://www.woothemes.com/products/woocommerce-subscriptions/).

Note que a integração não é feita com a API de pagamento recorrente da iugu e funciona totalmente a partir do WooCommerce Subscriptions, pois desta forma é possível obter maior controle sobre a assinatura dentro da sua loja WooCommerce.

### O pedido foi pago e ficou com o status de _processando_, e não _concluído_. Isso está certo? ###

Sim. Significa que o plugin está trabalhando como deveria.

Todo gateway de pagamento no WooCommerce deve mudar o status do pedido para _processando_ no momento em que o pagamento é confirmado. O status só deve ser alterado para _concluído_ após o pedido ter sido entregue.

Para produtos digitais, por padrão, o WooCommerce só permite o acesso do comprador quando o pedido tem o status _concluído_. No entanto, nas configurações do WooCommerce, na aba _Produtos_, é possível ativar a opção **Conceder acesso para download do produto após o pagamento**, liberando o download no status _processando_.

### Problemas com a integração? ###

1. Ative o **Log de depuração** nas configurações da iugu na administração do plugin do WooCommerce e tente realizar o pagamento novamente.
2. Copie o conteúdo do respectivo log no menu _WooCommerce > Status > Logs_.
3. Crie um [pastebin](http://pastebin.com) ou um [gist](http://gist.github.com) e salve o log.
4. Abra um ticket de suporte [aqui](http://wordpress.org/support/plugin/iugu-woocommerce) e compartilhe o link do log.

### Mais dúvidas sobre o funcionamento do plugin? ###

Abra um ticket para a sua pergunta [aqui](http://wordpress.org/support/plugin/iugu-woocommerce).


## Changelog ##

### 1.0.11 - 2017/11/28 ###
* **Melhoria**: Erros da API da iugu agora são exibidos na página do checkout em vez do antigo erro padrão de pagamento, que dizia muito sem dizer nada.
* **Correção**: Plugin não enviava o _Bairro_ do cliente, informação obrigatória para a criação de boletos registrados, impedindo a compra.

### 1.0.10 - 2016/06/30 ###

* Corrigido o ID de pagamento das assinaturas de cartão de crédito.
* Melhorado o funcionamento para Pessoa Jurídica, enviando o nome da empresa.

### 1.0.9 - 2016/06/18 ###

* Corrigido o campo de número de telefone.

### 1.0.8 - 2016/06/17 ###

* Corrigido suporte para WooCommerce 2.6+.
* Adicionado suporte para WooCommerce Subscriptions 2.0+.
* Corrigido suporte a assinaturas.
* Corrigida a exibição de CPF/CPNJ em boletos.

### 1.0.7 - 2016/02/09 ###

* Melhorada a geração das faturas, agora garantindo que seja pago apenas com cartão de crédito ou boleto, sem poder mudar a forma de pagamento.

### 1.0.6 - 2015/05/01 ###

* Melhorada a forma que os valores são convertidos para centavos antes de enviar para a API da iugu.
* Melhorado o campo de "Nome impresso no cartão" do formulário de cartão de crédito.
* Corrigido carregamento do JavaScript das opções de cartão de crédito quando instalado o WooCommerce Subscriptions.
* Correções no HTML das instruções do cartão de crédito após o pagamento.

### 1.0.5 - 2015/04/09 ###

* Corrigida opção de repasse de juros quando desativada.

### 1.0.4 - 2015/03/25 ###

* Corrigida as parcelas exibidas na versões 2.1 do WooCommerce.

### 1.0.3 - 2015/03/24 ###

* Melhorado o fluxo de pagamento com cartão de crédito.
* Corrigida a mudança de status quando o cartão é recusado.
* Melhorada as opções padrões do plugin.
* Corrigida as URLs das notificações.
* Removido o link de "Configurações" na página de plugins.

### 1.0.2 - 2015/03/12 ###

* Melhorada a renovação de assinaturas no WooCommerce Subscription.

### 1.0.1 - 2015/03/08 ###

* Adicionada opção para configurar a taxa de transação que é utilizada no repasse de juros do parcelamento.

### 1.0.0 - 2015/03/08 ###

* Versão inicial.

## Upgrade Notice ##

### 1.0.11  ###
Atualize seu plugin para não perder vendas no boleto bancário.
