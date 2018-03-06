=== WooCommerce iugu ===
Contributors: iugu, claudiosanches, braising, andsnleo
Tags: woocommerce, iugu, payment
Requires at least: 3.9
Tested up to: 4.9
Stable tag: 2.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Receba pagamentos por cartão de crédito e boleto bancário na sua loja WooCommerce com a iugu.

== Description ==

A iugu disponibiliza toda a infraestrutura necessária para que você possa transacionar pagamentos online com menos burocracia e mais vantagens. Com a nossa plataforma, você pode oferecer pagamentos com checkout transparente com cartão de crédito e boleto bancário. Para mais informações sobre o funcionamento da iugu, [leia a documentação](https://docs.iugu.com).

= Compatibilidade =

O **WooCommerce iugu** é compatível com:

* [WooCommerce 3.0+](https://wordpress.org/plugins/woocommerce/)
* [WooCommerce Subscriptions](http://www.woothemes.com/products/woocommerce-subscriptions/): para pagamentos recorrentes/assinaturas.
* [WooCommerce Pre-orders](http://www.woothemes.com/products/woocommerce-pre-orders/): para pré-venda de produtos.
* [WooCommerce Extra Checkout Fields for Brazil](https://br.wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/): permite enviar dados do cliente como **CPF** ou **CNPJ**, além dos campos **número** e **bairro** do endereço.

= Requerimentos =

* [Wordpress v3.9 ou superior](https://wordpress.org).
* [WooCommerce v3.0 ou superior](https://br.wordpress.org/plugins/woocommerce/).
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

= 2.0.2 =
* Correção: Algumas traduções para o português não funcionavam.

= 2.0.1 =
* Adição: Tradução do plugin para o português.

= 2.0.0 =
* Melhoria: Removidas as funções deprecadas do WooCommerce.
* Correção: Função responsável por identificar se o cliente é uma empresa não funcionava apropriadamente.

Veja o [changelog completo no Github](https://github.com/iugu/iugu-woocommerce/wiki).


== Upgrade Notice ==

= 2.0.2 =
Alguns usuários reportaram que tiveram problemas com algumas das traduções para o português liberadas na v2.0.1. Tentamos dar um jeito nisso nesta versão. Se, ainda assim, você não ver as   traduções direitinho, é só mandar um email para anderson@iugu.com.


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
