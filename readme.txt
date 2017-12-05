=== WooCommerce iugu ===
Contributors: iugu, claudiosanches, braising, andsnleo
Tags: woocommerce, iugu, payment
Requires at least: 3.9
Tested up to: 4.9
Stable tag: 1.0.13
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Receba pagamentos por cartão de crédito e boleto bancário na sua loja WooCommerce com a iugu.

== Description ==

A iugu disponibiliza toda a infraestrutura necessária para que você possa transacionar pagamentos online com menos burocracia e mais vantagens. Com a nossa plataforma, você pode oferecer pagamentos com checkout transparente com cartão de crédito e boleto bancário. Para mais informações sobre o funcionamento da iugu, [leia a documentação](https://docs.iugu.com).

= Compatibilidade =

O **WooCommerce iugu** é compatível com:

* [WooCommerce 2.1+](https://wordpress.org/plugins/woocommerce/)
* [WooCommerce Subscriptions](http://www.woothemes.com/products/woocommerce-subscriptions/): para pagamentos recorrentes/assinaturas.
* [WooCommerce Pre-orders](http://www.woothemes.com/products/woocommerce-pre-orders/): para pré-venda de produtos.
* [WooCommerce Extra Checkout Fields for Brazil](https://br.wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/): permite enviar dados do cliente como **CPF** ou **CNPJ**, além dos campos **número** e **bairro** do endereço.

= Requerimentos =

* [Wordpress v3.9 ou superior](https://wordpress.org).
* [WooCommerce v2.1 ou superior](https://br.wordpress.org/plugins/woocommerce/).
* [WooCommerce Extra Checkout Fields for Brazil](https://br.wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/).
* Conta ativa na [iugu](https://iugu.com/) com boleto bancário e/ou cartão de crédito habilitados como métodos de pagamento. Entenda [*O que é necessário para começar a usar iugu?*](https://support.iugu.com/hc/pt-br/articles/201531709).


== Installation ==

= 1. Instale o plugin =
Envie os arquivos do plugin para a pasta `wp-content/plugins` ou instale-o usando o instalador de plugins do WordPress. Em seguida, ative o **WooCommerce iugu**.

= 2. Obtenha o ID da sua conta iugu e um API token =
No painel da iugu, acesse o menu [*Administração > Configurações da conta*](https://app.iugu.com/account) e crie um *API token* do tipo LIVE. Ele será usado, junto com o ID da sua conta iugu, para configurar o plugin.

Você também pode criar um API token do tipo TEST para realizar testes com o plugin.

= 3. Configure o WooCommerce =
No WordPress, acesse o menu *WooCommerce > Configurações > Produtos > Inventário* e deixe em branco a opção **Manter estoque (minutos)**.

Essa funcionalidade, introduzida na versão 2.0 do Woocommerce, permite cancelar a compra e liberar o estoque depois de alguns minutos, mas não funciona muito bem com pagamentos por boleto bancário, pois estes podem levar até 48 horas para serem validados.

= 4. Ative os pagamentos pela iugu =

Ainda no WordPress, acesse o menu *WooCommerce > Configurações > Finalizar compra* e selecione **iugu - Cartão de crédito** ou **iugu - Boleto bancário**. Marque a caixa de seleção para ativar o(s) método(s) de pagamento que lhe interessa(m) e preencha as opções de **ID da conta** e **API Token** para cada um deles.


= Modo de testes =

É possível trabalhar com o plugin **WooCommerce iugu** em Sandbox (modo de testes). Dessa forma, você pode testar os processos de pagamentos por boleto e cartão antes de realizá-los em produção.

1. Acesse o painel da iugu e, no menu [*Administração > Configurações da conta*](https://app.iugu.com/account), crie um API token do tipo TEST.
2. Agora no WordPress, acesse as configurações de cartão de crédito ou boleto bancário do WooCommerce iugu (*WooCommerce > Configurações > Finalizar compra*) e adicione o seu API token (TEST).
3. Ao fim da página, marque a caixa de seleção *Ativar o sandbox da iugu*.

Opcional: Marque também a opção *Habilitar log* para ver o registro dos seus pagamentos no menu *WooCommerce > Status > Logs*.


== Frequently Asked Questions ==

= Qual é a licença do plugin? =

[GNU GPL (General Public Licence) v2](http://www.gnu.org/licenses/gpl-2.0.html).

= Do que preciso para utilizar o plugin? =

Ver [Requerimentos](https://github.com/iugu/iugu-woocommerce/wiki/Requerimentos).

= Quais são as tarifas da iugu? =

Conheça todas as tarifas da iugu em [iugu.com/precos](https://iugu.com/precos/).

= É possível utilizar a opção de pagamento recorrente/assinaturas? =

Sim, é possível utilizar este plugin para fazer pagamentos recorrentes com o [WooCommerce Subscriptions](https://www.woothemes.com/products/woocommerce-subscriptions/), que permite um maior controle sobre assinaturas dentro da sua loja WooCommerce.

= O pedido foi pago e ficou com o status *processando*, e não *concluído*. Isso está certo? =

Sim. Todo gateway de pagamento no WooCommerce deve mudar o status do pedido para *processando* no momento em que o pagamento é confirmado. O status só deve ser alterado para *concluído* após o pedido ter sido entregue.

Para produtos digitais, por padrão, o WooCommerce só permite o acesso do comprador quando o pedido tem o status *concluído*. No entanto, nas configurações do WooCommerce, na aba *Produtos*, é possível ativar a opção **Conceder acesso para download do produto após o pagamento**, liberando o download no status *processando*.


== Changelog ==

= 1.0.13 - 2017/12/4 =
* **Adição**: As chamadas de API da iugu agora recebem a versão do plugin utilizada para facilitar o debugging e o suporte.

= 1.0.12 - 2017/11/30 =
* **Correção**: Incluída dependência do plugin que estava em falta na versão 1.0.11. Obrigado, @diasnt!

= 1.0.11 - 2017/11/29 =
* **Melhoria**: Erros da API da iugu agora são exibidos na página do checkout em vez do antigo erro padrão de pagamento, que dizia muito sem dizer nada.
* **Correção**: Plugin não enviava o *Bairro* do cliente, informação obrigatória para a criação de boletos registrados, impedindo a compra.

= 1.0.10 - 2016/06/30 =
* **Correção**: ID de pagamento das assinaturas de cartão de crédito.
* **Melhoria**: Funcionamento para pessoa jurídica, enviando o nome da empresa.

= 1.0.9 - 2016/06/18 =
* **Correção**: Campo de número de telefone.

= 1.0.8 - 2016/06/17 =
* **Correção**: Suporte para WooCommerce 2.6+.
* **Novidade**: Suporte para WooCommerce Subscriptions 2.0+.
* **Correção**: Suporte para assinaturas.
* **Correção** Exibição de CPF/CPNJ em boletos.

= 1.0.7 - 2016/02/09 =
* **Melhoria**: Geração das faturas, garantindo que sejam papgas apenas com cartão de crédito ou boleto, sem poder mudar a forma de pagamento.

= 1.0.6 - 2015/05/01 =
* **Melhoria**: Conversão de valores para centavos antes de enviá-los para a API da iugu.
* **Melhoria**: Campo de "Nome impresso no cartão" do formulário de cartão de crédito.
* **Correção**: Carregamento do JavaScript das opções de cartão de crédito quando instalado o WooCommerce Subscriptions.
* **Correção**: HTML das instruções do cartão de crédito após o pagamento.

= 1.0.5 - 2015/04/09 =
* **Correção**: Opção de repasse de juros quando desativada.

= 1.0.4 - 2015/03/25 =
* **Correção**: Parcelas exibidas na versões 2.1.x do WooCommerce.

= 1.0.3 - 2015/03/24 =
* **Melhoria**: Fluxo de pagamento com cartão de crédito.
* **Correção**: Mudança de status quando o cartão é recusado.
* **Melhoria**: Opções padrões do plugin.
* **Correção**: URLs das notificações.
* **Melhoria**: Link de *Configurações* na página de plugins.

= 1.0.2 - 2015/03/12 =
* **Melhoria**: Renovação de assinaturas no WooCommerce Subscription.

= 1.0.1 - 2015/03/08 =
* **Adição**: Opção para configurar a taxa de transação que é utilizada no repasse de juros do parcelamento.

= 1.0.0 - 2015/03/08 =
* Lançamento da versão inicial.


== Upgrade Notice ==

= 1.0.13 =
Coisa rápida: incluímos a versão do iugu WooCommerce nas chamadas da nossa API para ajudar o desenvolvimento e o suporte do plugin. 


== Suporte ==

= Canais =
* [Issues no Github](https://github.com/iugu/iugu-woocommerce/issues)
* [Fórum de suporte no Wordpress](https://wordpress.org/support/plugin/iugu-woocommerce)
* Atendimento da iugu:
  * Email: [suporte@iugu.com](mailto:suporte@iugu.com)
  * Chat online: Disponível para clientes iugu em [app.iugu.com](https://app.iugu.com) (seg. à sex., das 9h às 17h).

= Compartilhando os logs =
1. Na administração do plugin do WooCommerce, acesse as configurações de cartão de crédito ou de boleto da iugu, ative o **Log de depuração** e tente realizar o pagamento novamente. Caso o log já esteja ativado, procure o número do pedido feito pelo comprador.
2. Copie o log referente ao número do pedido no menu *WooCommerce > Status > Logs*.
3. Crie um [pastebin](http://pastebin.com) ou um [gist](http://gist.github.com) e salve o log para gerar um link público de compartilhamento.

= Outras dúvidas =
Para dúvidas específicas sobre a iugu, acesse nossa [base de conhecimento](https://support.iugu.com).

== Colabore ==

Você pode contribuir para o desenvolvimento do plug-in fazendo o fork do repositório no [GitHub](https://github.com/iugu/iugu-woocommerce).
