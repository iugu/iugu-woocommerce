=== WooCommerce Iugu ===
Contributors: iugu, claudiosanches, braising
Tags: woocommerce, iugu, payment
Requires at least: 3.9
Tested up to: 4.1
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Receba pagamentos por cartão de crédito e boleto bancário com o Iugu

== Description ==

Iugu é uma plataforma que disponibiliza toda infra-estrutura necessária para que você possa transacionar pagamentos online com menos burocracia e mais vantagens.

Com a iugu você pode oferecer pagamento com o checkout transparente com cartão de crédito e boleto bancário.

Saiba mais como o Iugu funciona em [Iugu - Entendendo como tudo funciona](https://iugu.com/documentacao/comecando).

= Compatibilidade =

Compatível com as versões 2.1.x e 2.2.x e 2.3.x do WooCommerce.

Este plugin funciona integrado com o [WooCommerce Extra Checkout Fields for Brazil](http://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/), desta forma é possível enviar documentos do cliente como "CPF" ou "CNPJ", além dos campos "número" e "bairro" do endereço.

Também é compatível com os seguintes plugins:

* [WooCommerce Subscriptions](http://www.woothemes.com/products/woocommerce-subscriptions/) - Para pagamentos recorrentes/assinaturas.
* [WooCommerce Pre-orders](http://www.woothemes.com/products/woocommerce-pre-orders/) - Para pré venda de produtos.

= Instalação =

Confira o nosso guia de instalação e configuração do Iugu na aba [Installation](http://wordpress.org/extend/plugins/iugu-woocommerce/installation/).

= Dúvidas? =

Você pode esclarecer suas dúvidas usando:

* A nossa sessão de [FAQ](http://wordpress.org/extend/plugins/iugu-woocommerce/faq/).
* Criando um tópico no [fórum de público do WordPress](http://wordpress.org/support/plugin/iugu-woocommerce).
* Criando um tópico no [fórum do Github](https://github.com/iugu/iugu-woocommerce/issues).

= Coloborar =

Você pode contribuir com código-fonte em nossa página no [GitHub](https://github.com/iugu/iugu-woocommerce).

== Installation ==

= Instalação do plugin: =

* Envie os arquivos do plugin para a pasta wp-content/plugins, ou instale usando o instalador de plugins do WordPress.
* Ative o plugin.

= Requerimentos: =

É necessário possuir uma conta no [Iugu](https://iugu.com/) e ter instalado o [WooCommerce](http://wordpress.org/extend/plugins/woocommerce/).

= Configuração no Iugu =

Você deve pegar o seu **ID da conta** e gerar uma **API Token** (do tipo _LIVE_) dentro das configurações da [sua conta no Iugu](https://iugu.com/settings/account) para utilizar neste plugin.

Além disso é necessário habilitar o cartão de crédito e o boleto bancário na sua conta em [Administração > Métodos de Pagamento](https://iugu.com/a/account_payments).

Note que para aceitar pagamento com carao de crédito é necessário enviar uma certa documentação para o Iugu, veja mais detalhes em: [Qual é a documentação necessária para aceitar pagamento com cartão crédito?](http://support.iugu.com/hc/pt-br/articles/202342429-Qual-%C3%A9-a-documenta%C3%A7%C3%A3o-necess%C3%A1ria-para-aceitar-pagamento-com-cart%C3%A3o-cr%C3%A9dito-)

= Configurações do Plugin: =

Com o plugin instalado acesse o admin do WordPress e entre em "WooCommerce" > "Configurações" > "Finalizar compra", selecione "Iugu - Cartão de crédito" ou "Iugu - Boleto bancário".

Habilite o Iugu, preencha as opções de **ID da conta** e **API Token** (você conseguiu estas informações no passo anterior, além que você precisa gerar apenas uma **API Token** que deve ser utilizada nas duas formas de pagamento).

Note que o plugin divide as duas formas de pagamento, permitindo você ativar e configurar o que desejar.

= Configurações de cartão de crédito =

Entre as opções do cartão de crédito é possível configurar o número de parcelas que os clientes poderam dividir, esta opção não pode ser maior do que o valor configurado dentro da sua conta do Iugu.

É possível também manipular a exibição do valor das parcelas com as opções de "Repassar juros", "Sem juros" e "Taxa por transação".

Note que estas opções são apenas de exibição e não configura de nenhuma forma o valor total que o cliente realmente irá pagar, pois o que controla isto são as configurações de parcelas da sua conta do Iugu e desta forma as opções de "Repassar juros", "Sem juros", "Taxa por transação" devem represetar exatamente o que você configurou na sua conta do Iugu.

Para saber mais sobre parcelamento e juros no Iugu leia o tutorial: [Existem juros no parcelamento?](http://support.iugu.com/hc/pt-br/articles/201728767-Existem-juros-no-parcelamento-).

= Configurações no WooCommerce =

No WooCommerce 2.0 ou superior existe uma opção para cancelar a compra e liberar o estoque depois de alguns minutos.

Esta opção não funciona muito bem com o boleto bancário, pois pagamentos por boleto bancário pode demorar até 48 horas para serem validados.

Para corrigir isso é necessário ir em "WooCommerce" > "Configurações" > "Produtos" > "Inventário" e limpar (deixe em branco) a opção **Manter Estoque (minutos)**.

Pronto, sua loja já pode receber pagamentos pelo Iugu.

= Sandbox =

É possível tambeḿ trabalhar com o plugin no modo sandbox, desta forma você pode testar pagamentos antes de realmente utilizar o plugin em produção.

Para utilizar em modo Sandbox você deve criar uma **API Token** do tipo _TEST_ dentro da [sua conta no Iugu](https://iugu.com/settings/account) e ativar a opção "Sandbox do Iugu" no plugin.

Quando for trocar para produção você deve desmarcar a opção e adicionar uma **API Token** do tipo _LIVE_.

== Frequently Asked Questions ==

= Qual é a licença do plugin? =

Este plugin esta licenciado como GPL.

= O que eu preciso para utilizar este plugin? =

* Ter instalado o plugin WooCommerce 2.1 ou superior.
* Possuir uma conta no [Iugu](https://iugu.com/).
* Pegar o seu **ID de conta** e gerar uma **API Token** na página de [sua conta no Iugu](https://iugu.com/settings/account).
* Desativar a opção **Manter Estoque (minutos)** do WooCommerce.

Note que você NÃO PRECISA configurar qualquer GATILHO dentro da sua conta do Iugu!

= Quais são as tarifas do Iugu =

Veja as tarifas em [Preços e Tarifas - Iugu](http://mkt.iugu.com/precos/).

= É possível utilizar a opção de pagamento recorrente/assinaturas? =

Sim, é possível utilizar este plugin para fazer pagamento recorrente integrado com o [WooCommerce Subscriptions](http://www.woothemes.com/products/woocommerce-subscriptions/).

Note que a integração não é feita com a API de pagamento recorrente do Iugu e funciona totalmente a partir do WooCommerce Subscriptions, pois desta forma é possível obter maior controle sobre a assinatura dentro da sua loja WooCommerce.

= O pedido foi pago e ficou com o status de "processando" e não como "concluído", isto esta certo ? =

Sim, esta certo e significa que o plugin esta trabalhando como deveria.

Todo gateway de pagamentos no WooCommerce deve mudar o status do pedido para "processando" no momento que é confirmado o pagamento e nunca deve ser alterado sozinho para "concluído", pois o pedido deve ir apenas para o status "concluído" após ele ter sido entregue.

Para produtos baixáveis a configuração padrão do WooCommerce é permitir o acesso apenas quando o pedido tem o status "concluído", entretanto nas configurações do WooCommerce na aba *Produtos* é possível ativar a opção **"Conceder acesso para download do produto após o pagamento"** e assim liberar o download quando o status do pedido esta como "processando".

= Problemas com a integração? =

Primeiro de tudo ative a opção **Log de depuração** e tente realizar o pagamento novamente.
Feito isso copie o conteúdo do log e salve usando o [pastebin.com](http://pastebin.com) ou o [gist.github.com](http://gist.github.com), depois basta abrir um tópico de suporte [aqui](http://wordpress.org/support/plugin/iugu-woocommerce).

= Mais dúvidas relacionadas ao funcionamento do plugin? =

Abra um tópico para a sua pergunta [aqui](http://wordpress.org/support/plugin/iugu-woocommerce).

== Screenshots ==

1. Configurações do método de cartão de crédito.
2. Configurações do método de boleto bancário.
2. Opção de cartão de crédito na página de finalizar compra.
2. Opção de boleto bancário na página de finalizar compra.

== Changelog ==

= 1.0.1 - 2015/03/08 =

* Adicionada opção para configurar a taxa de transação que é utilizada no repasse de juros do parcelamento.

= 1.0.0 - 2015/03/08 =

* Versão inicial.

== Upgrade Notice ==

= 1.0.1 =

* Adicionada opção para configurar a taxa de transação que é utilizada no repasse de juros do parcelamento.

== License ==

WooCommerce Iugu is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

WooCommerce Iugu is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with WooCommerce Iugu. If not, see <http://www.gnu.org/licenses/>.
