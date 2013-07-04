repo=$1
git remote add -f $repo git@github.com:phpalchemy/$repo.git
git merge -s ours --no-commit $repo/master
git read-tree --prefix=Alchemy/Component/$repo/ -u $repo/master
git commit -m "Merge branch 'master' of github.com:phpalchemy/$repo"

# this is to update the subtrees
#git pull -s subtree $repo master

echo ""
echo "DONE! -> tracked subtree ($repo)"
