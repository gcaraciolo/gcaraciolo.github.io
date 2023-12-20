---
extends: _layouts.post
section: content
title: "O custo da má nomenclatura"
date: 2021-10-30
description: O custo da má nomenclatura em projetos de software
---

Você já precisou alterar um software em que existem métodos com assinaturas do tipo `setSomething, add, remove`, etc;
e dentro desses métodos existem dezenas de linha de código com regras de negócio complexas?

Nessas situações, a sensação que tenho ao ler o código é de como se eu fosse um intérprete por ter que ler todas as
regras de negócio, separando mentalmente o que cada bloco de código faz, correlacionando com os requisitos do software,
para só então realmente entender o significado e fazer as alterações necessárias.

Nomear os diversos elementos em um projeto de software é difícil. Tão difícil que Phil Karlton afirmava que "existem apenas
duas coisas difíceis em ciências da computação: invalidação de cache e nomear coisas".

Mas por que é tão difícil dar nome aos elementos que compõem um software? Para tentar achar uma resposta para essa
pergunta, vamos analisar duas versões de um pedaço de código. Trata-se de um código para estender o período ativo
de uma assinatura. Iremos lidar com as entidades `pagamento, plano, pacote, conta e assinatura`.

Código ruim:


```php
<?php

class PaymentService
{
    public function add(Payment $payment)
    {
        $plan = Plan::with('package')->firstOrFail($payment->plan_id);
        $account = Account::firstOrFail($payment->account_id);
        $subscription = $account->subscription();

        if ($this->ends_at->isBefore(now())) {
            $subscription->ends_at = now()->addDays($plan->days);
        } else {
            if ($plan->package->price != $subscription->package->price) {
                $newDays = ceil($subscription->package->price * $subscription->remainingDays() / $plan->package->price) + $plan->days;
                $subscription->ends_at = now()->addDays($newDays);
            } else {
                $subscription->ends_at = $subscription->ends_at->addDays($plan->days);
            }
        }

        $subscription->save();
    }
}
```

Para entender as regras de negócio nessa versão foi necessário entender os detalhes de implementação.
O código é difícil de ler, difícil de testar, está cheio de acoplamento e carece de abstração.

## Falta de abstração e Encapsulamento

Para entender o método foi necessário entender como os objetos são encontrados no banco de dados, como é calculado a diferença
entre pacotes com preços diferentes e como é realizado o cálculo de dias a serem adicionados numa assinatura.

O método também viola o princípio de Ocultamento de Informação, onde os dados da classe `Subscription` são alterados por uma outra
classe. Esse tipo de código torna o sistema inflexível e imprevisível, já que diversas classes Clientes podem alterar os
dados da classe Subscription.

Ter que passar por esse processo todas as vezes que vamos alterar um software é custoso e com o tempo cansa. Logo os
desenvolvedores estarão lendo meias palavras, assumindo um comportamento inexistente, e introduzindo bugs, por não
terem entendido como aquela parte do software funciona e também como outras partes são afetadas pelo código, gerando
efeitos colaterais inesperados.

## Efeito colateral

O método `add` é responsável por recuperar os dados do mecanismo de persistência, realizar diversos cálculos e verificações
sobre os dias que devem ser adicionados numa assinatura e em seguida salvar os dados (ps: estamos utilizado o ActiveRecord
nesse exemplo, que NÃO é um problema).

À medida que novos requisitos chegam, as alterações que são feitas podem gerar efeitos colaterais inesperados.
Precisamos de uma forma de testar os cenários de cálculo extensão do período ativo de uma assinatura sem nos preocupar
em como os dados são recuperados ou persistidos.

Vamos começar a resolver esses problemas utilizando os nomes que geralmente utilizamos na nossa cabeça mas que não estão
refletidos no código.

## Nomeando coisas

Algo que incomoda é ver a atribuição `$subscription->ends_at` sendo feita fora do modelo `Subscription`. Iniciaremos
adicionando encapsulamento nessa alteração através do método `extendActivePeriod`. Se você pensou no método `setEndsAt`,
repense, pois é exatamente esse tipo de nomenclatura que queremos evitar!

```php
<?php

class Subscription
{
    public function extendActivePeriod()
    {}
}
```

Agora podemos extrair a responsabilidade de calcular quantos dias devem ser adicionados à assinatura em um outro método.
Na verdade, queremos realmente calcular quando será a próxima data de cobrança dado uma possível alteração no plano
contratado. O nome `calculateNextBillingDate` me parece um bom candidato. Vamos usá-lo, e já aproveitaremos para fazer
mais algumas melhorias, tentando deixar o código mais explícito.

Uma melhor versão:

```php
<?php

class PaymentService
{
    public function add(Payment $payment)
    {
        $payment->account->subscription->extendPeriod($payment->plan);
    }
}

class Subscription extends Model
{
    /** @var Carbon */ private $ends_at;
    /** @var Package */ private $package;

    public function extendPeriod(Plan $plan)
    {
        $nextBillingDate = $this->calculateNextBillingDate($plan);
        $this->ends_at = $nextBillingDate;
        $this->save();
    }

    public function calculateNextBillingDate($plan)
    {
        if ($this->expired()) {
            return now()->addDays($plan->days);
        } elseif ($this->package->isEquals($plan->package)) {
            return $this->ends_at->addDays($plan->days);
        } else {
            return $this->ends_at->addDays($this->proportionalDaysFor($plan));
        }
    }

    public function expired(): bool
    {
        return $this->ends_at->isBefore(now());
    }

    public function proportionalDaysFor(Plan $plan)
    {
        return $this->package->calculateProportionalDays($this->remainingDays(), $plan->package) + $plan->days;
    }

    public function remainingDays(): int
    {
        if (!$this->expired()) {
            return now()->diffInDays($this->ends_at);
        }
        return 0;
    }
}

class Package
{
    public function calculateProportionalDays(int $days, Package $otherPackage): int
    {
        return (int) ceil($this->price * $days / $otherPackage->price);
    }

    public function isEquals(Package $otherPackage): bool
    {
        return $otherPackage->price != $this->price;
    }
}
```

Um.. Parece que a quantidade de linhas no código aumentou bastante. Mas.. quem falou que poucas linhas de código
significam maior legibilidade? (Na verdade já vi discussões que arrow functions `() => {}` são mais legíveis que utilizar
`function () {}` ...)

Com essa versão melhorada, você consegue pensar no cálculo de dias proporcionais que devem ser adicionados numa assinatura
que teve o plano alterado, abstraindo as outras responsabilidades do código? Se sim, esse é o objetivo.
Um bom código tem interfaces reveladoras de intenção que abstraem os detalhes de implementação.

Além disso, agora você pode, e na verdade deve, se livrar o PaymentService, pois esse já não tem mais razão para existir.
A criação desse tipo de classe só faz com que o software contenha modelos anêmicos.

Agora, caso você esteja se perguntando como chegamos nesses nomes, e esteja pensando onde está a resposta para dar
melhores nomes aos elementos que compõe um software, aqui vão algumas dicas:

1. Escute bem os termos utilizados pelos experts no domínio do software.
   Converse constantemente com as pessoas que entendem do domínio em que o software está inserido. Mas, não se limite
   apenas a isso. Busque mais informações na Internet, livros, etc. Ao conversar com outros desenvolvedores sobre
   o software utilize os mesmos termos e aplique essa mesma linguagem no código. Evite criar maps de tradução
   e estabeleça uma linguagem ubíqua. Você irá aprender muito mais sobre isso no livro Domain Driven Design de Eric Evans.

2. Tenha domínio sobre princípios de design de software.
   Comece dominando bem as boas práticas de programação orientada a objetos e os princípios SOLID. Permita que seu
   código esteja pronto para ser estendido sem alterar muito do código que já existe.

3. Crie abstrações.
   Não deixe que os detalhes de implementação afetem as regras de negócio.

## Conclusão

Um código ruim se revela quando não são utilizados bons nomes em métodos, variáveis, classes, etc. Lidar com esse tipo
de código diariamente reduz a produtividade do time e diminui a qualidade do software.

A falta de abstração obriga os programadores a lerem os detalhes de implementação de todos os casos de uso, inclusive
dos que não estão relacionados diretamente com o escopo da atividade atual, por causa dos efeitos colaterais. Esse tempo gasto reflete diretamente nos custos de manutenção do software.

A introdução de abstrações no código pode ser feita ao criar limites bem definidos de responsabilidade para cada trecho
de código e nomear estes com os termos utilizados pelos experts no domínio que o software opera.

## Sugestão: Exercício de fixação

1. Analise o código abaixo. Esse código é responsável pelo compartilhamento de uma publicação
   em um Blog. Um novo requisito chegou, solicitando que publicações com mais de um mês após o primeiro rascunho,
   não devem ser compartilhadas. Descreva quais os problemas que esse código tem e faça as alterações necessárias para corrigi-lo.

```php
/** @property Carbon $created_at Post's creation date */
/** @property string $status Could be draft|shared|canceled */;
class Post extends Model
{
    protected $fillable = ['date', 'status'];
}


class PublishService
{
    /** @var BlogService */ private $blogService;

    public function __construct(BlogService $blogService)
    {
        $this->blogService = $blogService;
    }

    public function publish(Post $post): void
    {
        if ($this->isDelayedPost($post)) {
            $this->cancelDelayedPost($post);
            return;
        }

        $this->blogService->sendPost($post);
    }

    private function isDelayedPost(Post $post): bool
    {
        return now()->subMonth()->greaterThan($post->created_at);
    }

    private function cancelDelayedPost(Post $post): void
    {
        $post->update(['status' => 'canceled']);
        event(new DelayedPostCanceled($post));
    }
}

class DelayedPostCanceled
{
    /** @var Post */ private $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }
}
```