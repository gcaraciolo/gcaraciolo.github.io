---
extends: _layouts.post
section: content
title: "Reflexão sobre responder a mudanças"
date: 2023-04-11
---

Ontem na pós, a turma foi dividida em quatro grupos e cada grupo recebeu um dos princípios ágeis para debater. O meu grupo ficou com o princípio "Responder a mudanças no lugar de seguir um plano".

O curso tem pessoas de diversas áreas e nem todas são relacionadas a tecnologia, portanto as pessoas têm experiências e vivências diferentes.

Eu comecei a provocação para gente ir soltando ideias e colocando no chat do zoom o que achávamos que entendemos por esse princípio. E aí tivemos as seguintes sentenças:
- Adaptar o orçamento do projeto diante da diminuição de recursos disponíveis.
- Mudas as prioridades a partir de nova demanda trazida pelo cliente.
- Adaptabilidade do trabalho de hoje a realidade do cliente no momento que ele está. 
- Prioridades que mudam de acordo com a maturidade do projeto.
- Testar partes e adaptar de acordo com o feedback
- Refazer o projeto original em decorrência de medida normativa de legislação que altera o cenário inicialmente imaginado.
- Redirecionar a meta de inovação no momento em que a concorrência lança um produto com características semelhantes ao projeto em desenvolvimento.
- Incentivar e formar equipes resilientes e adaptáveis, atentas a mudanças internas e externas.
- Responder ao cenário de mudanças com uma rápida tomada de decisão, principalmente às externalidades.
- Refazer a linha de campanha publicitária em função da mudança de preço do produto que inviabilizaria a proposta da campanha.

Além disso, também falamos sobre o papel da liderança nesse contexto e de equipes autogerenciáveis. Discutimos brevemente sobre como equipes autogerenciáveis conseguem responder a mudanças sem precisar de um ator central, fazendo o papel de líder. E a importância de um líder quando a equipe não é madura o suficiente para ser auto gerenciável; como o líder consegue dar a visão ao time sobre a importância de responder às demandas e alterar um plano de última hora.

A discussão foi muito legal, mas o tempo foi curto para nos aprofundarmos em como ter um ambiente que seja flexível o suficiente para atender a esse princípio. E ainda acho que falhei em não ter passado para os meus colegas um pouco de conhecimento a respeito disso.

Tudo que falamos e escrevemos está muito relacionado a como as pessoas interpretam a palavra **ágil**. No exemplo da campanha publicitária, por exemplo, falamos que o princípio se aplicaria com: ou uma equipe autogerenciável que entenderia a importância de atender a solicitação do cliente e trabalhar acima do esperado para entregar o que foi solicitado de última hora, ou com um líder fazendo esse papel de dar a visão para a equipe da importância do que precisa ser feito.

Ambas as opiniões nada tem a ver com o manifesto ágil. Nada adianta ter coragem para trabalhar duro com solicitações de última hora. Não é isso que se espera da agilidade. Tão pouco a velocidade que um determinado indivíduo consegue entregar as alterações a tempo.

### Como responder a mudanças de forma ágil
A agilidade vem das práticas e disciplinas que o time ágil tem na cultura. Quando estamos falando de times que trabalham com desenvolvimento de software, o princípio se traduz basicamente na velocidade que esse time consegue responder a alterações nos requisitos do projeto. Quem já trabalhou com software sabe que não é simplesmente: "ah, blz.. a gente vai refazer os requisitos..". O custo é alto e o risco maior ainda. O mais provável que aconteça é a geração de defeitos no software e a incompletude do que foi pedido, aumentando ainda mais a insatisfação do cliente.

Algumas das disciplinas que um time de desenvolvimento de software ágil tem são:
- Integração e implantação contínua do que está sendo construído com o código principal;
- Escrita e execução contínua de testes automatizados;
- Refatoração do código para que este sempre represente os conceitos e abstrações mais fiéis ao domínio;
- Estudo contínuo de negócio do cliente para ter capacidade de negociar escopo;
- Métricas claras para identificar problemas rapidamente.


### Integração e implantação contínua
Uma dificuldade de realizar mudanças de forma ágil é quando o projeto está estruturado de uma forma que seus componentes só são integrados algumas vezes durante o ciclo de desenvolvimento do projeto. Geralmente em lugares assim, essas integrações são penosas. Quando a implantação é feita, bugs em diferentes lugares da aplicação aparecem.

Em um time ágil, a integração e implantação do código é feita de forma contínua, de preferência várias vezes ao dia. Obviamente não é só por isso que problemas são evitados. Porém nesse ambiente o time cria mecanismos para que as integrações e implantações ocorram da forma mais suave possível.

O time terá a disciplina de todo trabalho feito passar sempre pela revisão de um colega; deverá também ter ferramentas que devem reverter a alteração no ambiente de produção caso algum distúrbio seja notificado nas métricas do projeto; irá conectar o projeto com ferramentas que irão identificar problemas e defeitos em tempo real (métricas claras). O time também irá entender a importância de ter testes automatizados e da refatoração.

### Testes automatizados e refatoração
Na minha opinião essas são as principais práticas do time ágil. No ambiente em que os testes de software são feitos manualmente, qualquer mudança gera uma cascata de problemas que demoram bastante para serem corrigidos. E quando um código não representa de forma clara o domínio que está inserido, existe uma dificuldade imensa na leitura do código e interpretações erradas passam a acontecer com frequência.

Os testes automatizados são executados na ordem de segundos e garantem que comportamentos conhecidos aconteçam de forma previsível. Para exemplificar isso imaginem um software que faz o cálculo de seguro. Quando uma regra do seguro muda, o software precisa ser alterado. Os testes automatizados irão rodar os diversos caminhos que podem ser feitos pelo software para chegar num resultado. Aplicar a mudança se torna mais seguro. "Basta" alterar a bateria de testes e fazer o software responder a esse novo cenário. Não é fácil, mas é extremamente mais ágil e seguro do que ter que rodar todos os possíveis cenários de cálculo manualmente.

E se torna ainda mais fácil se o projeto está bem feito no que diz respeito a refatoração do código. Se os requisitos mudam, os conceitos mudam. Portanto o software deve estar escrito de acordo com esses conceitos. Boas abstrações são identificadas ao longo do projeto e se deixadas de lado, cada nova alteração se torna mais complexa de fazer, ao ponto de simplesmente ser penosa.

### Conhecimento do negócio e métricas claras
Um time ágil identifica problemas em tempo real. Se o software está com defeito, terá uma notificação em algum lugar mostrando isso. Mas não só os defeitos são monitorados. Se algo vai bem, o time ágil também tem gráficos temporais mostrando o que vai bem.

Isso permite que o time converse com os clientes e experts no domínio mostrando como o software está sendo utilizado pelos usuários. Empresas pivotam a solução baseada nesse conhecimento. A inovação de um negócio acontece na melhoria contínua de tais métricas.


### E fora da área de software?
Dado esses exemplos de práticas e disciplinas de um time ágil de desenvolvimento de software, como podemos transferir esse conhecimento para as outras áreas? Pegando as sentenças colocadas pelo meu grupo, na minha ignorância em tais áreas, consigo imaginar em algumas coisas.

No cenário de uma agência de marketing digital, onde o cliente pede para que uma alteração de preço seja feita de última hora o time deve estar inserido no seguinte contexto para ser ágil:

Ter uma ferramenta que, dado um comando, todos os artefatos sejam atualizados. Também ter um fluxo claro de aprovação dos artefatos; uma ferramenta que seja capaz de publicar esses artefatos nos diferentes canais de comunicação e se necessário, editar o conteúdo.	
O time deve ter métricas claras de como as pessoas estão reagindo a campanha e se adaptar. Se não está havendo nenhum engajamento, o time deve entender isso logo no início da campanha. Se estiver tendo uma recepção negativa, o time tem o plano B pronto para contornar a situação rapidamente.
O time experimenta diferentes formas de divulgação e ajuda o cliente a tomar a melhor decisão de como fazer uma campanha... 



