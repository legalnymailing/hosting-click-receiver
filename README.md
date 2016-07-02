# Unsubscribe

To repozytorium zawiera skrypty bezpoœrednio odpowiedzialne za obs³ugê klikniêæ w linki Unsubscribe w wysy³anych mailach.

Klikniêcia te kierowane s¹ na zewnêtrzny hosting, który ³¹czy siê z w³aœciw¹ instancj¹ AkoMaila. Dziêki temu docelowy klient nie musi mieæ bezpoœredniego dostêpu do instancji AkoMaila, przez co odpada wiele potencjalnych problemów z bezpieczeñstwem.

# Tracking

Informacje o wyœwietleniach maili odk³adaj¹ siê w logach webserwera - te nastêpnie s¹ codzienne odbierane przez skrypt z repozytorium lm-forwarder, który nastêpnie na podstawie ich zawartoœci symuluje wyœwietlenia, wykonuj¹c requesty narzêdziem wget do w³aœciwej instancji AkoMaila.
