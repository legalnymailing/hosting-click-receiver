# Unsubscribe

To repozytorium zawiera skrypty bezpo�rednio odpowiedzialne za obs�ug� klikni�� w linki Unsubscribe w wysy�anych mailach.

Klikni�cia te kierowane s� na zewn�trzny hosting, kt�ry ��czy si� z w�a�ciw� instancj� AkoMaila. Dzi�ki temu docelowy klient nie musi mie� bezpo�redniego dost�pu do instancji AkoMaila, przez co odpada wiele potencjalnych problem�w z bezpiecze�stwem.

# Tracking

Informacje o wy�wietleniach maili odk�adaj� si� w logach webserwera - te nast�pnie s� codzienne odbierane przez skrypt z repozytorium lm-forwarder, kt�ry nast�pnie na podstawie ich zawarto�ci symuluje wy�wietlenia, wykonuj�c requesty narz�dziem wget do w�a�ciwej instancji AkoMaila.
