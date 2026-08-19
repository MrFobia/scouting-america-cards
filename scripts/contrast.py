def lum(h):
    h=h.lstrip('#'); r,g,b=[int(h[i:i+2],16)/255 for i in (0,2,4)]
    f=lambda c: c/12.92 if c<=0.03928 else ((c+0.055)/1.055)**2.4
    return 0.2126*f(r)+0.7152*f(g)+0.0722*f(b)
def cr(a,b):
    la,lb=lum(a),lum(b); hi,lo=max(la,lb),min(la,lb)
    return round((hi+0.05)/(lo+0.05),2)
# Paleta Scouting America (pags. 14 y 15). Sin dorado: es de la sub-marca Cub Scouts.
C={'red':'#CE1126','blue':'#003F87','tan':'#D6CEBD','gray':'#515354','white':'#FFFFFF',
   'paleblue':'#9AB3D5','darkblue':'#003366','lighttan':'#E9E9E4','darktan':'#AD9D7B',
   'palegray':'#858787','darkgray':'#232528'}
pairs=[('darkgray','white'),('darkblue','white'),('blue','white'),('gray','white'),
       ('red','white'),('white','blue'),('white','red'),('white','darkblue'),
       ('blue','lighttan'),('blue','tan'),('darkgray','lighttan'),('red','tan'),
       ('paleblue','darkblue'),('darktan','white'),('palegray','white')]
print(f"{'par':28} {'ratio':>6}  AA-normal(4.5)  AA-large(3.0)")
for a,b in pairs:
    r=cr(C[a],C[b])
    print(f"{a+'/'+b:28} {r:>6}  {'PASA' if r>=4.5 else 'FALLA':^14}  {'PASA' if r>=3 else 'FALLA':^13}")
